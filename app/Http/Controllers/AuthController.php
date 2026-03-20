<?php

namespace App\Http\Controllers;

use App\Http\Requests\Auth\ChangePasswordRequest;
use App\Http\Requests\Auth\ForgotPasswordRequest;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Requests\Auth\ResetPasswordRequest;
use App\Http\Resources\UserResource;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;

class AuthController extends Controller
{
    /**
     * Register user
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        $employee = null;

        if ($request->filled('employee_id')) {
            $employee = Employee::find($request->integer('employee_id'));

            if (!$employee) {
                return $this->notFoundResponse('Không tìm thấy nhân viên');
            }

            $existingAccount = User::where('employee_id', $employee->id)->exists();
            if ($existingAccount) {
                return $this->errorResponse('Nhân viên này đã có tài khoản', 422);
            }
        }

        $user = User::create([
            'employee_id' => $employee?->id,
            'email' => strtolower((string) $request->email),
            'password' => $request->password,
            'role' => 3,
            'status' => 1,
            'email_verified_at' => now(),
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return $this->successResponse([
            'user' => new UserResource($user),
            'token' => $token,
            'token_type' => 'Bearer',
        ], 'Đăng ký thành công', 201);
    }

    /**
     * Login user
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $credentials = $request->only('email', 'password');

        if (!Auth::attempt($credentials)) {
            return $this->unauthorizedResponse('Email hoặc mật khẩu không chính xác');
        }

        $user = Auth::user();

        // Check if user is active
        if (!$user->isActive()) {
            Auth::logout();
            return $this->forbiddenResponse('Tài khoản của bạn đã bị khóa');
        }

        // Create token
        $token = $user->createToken('auth_token')->plainTextToken;

        return $this->successResponse([
            'user' => new UserResource($user),
            'token' => $token,
            'token_type' => 'Bearer',
        ], 'Đăng nhập thành công');
    }

    /**
     * Logout user
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return $this->successResponse(null, 'Đăng xuất thành công');
    }

    /**
     * Get current user info
     */
    public function me(Request $request): JsonResponse
    {
        return $this->successResponse(
            new UserResource($request->user()),
            'Lấy thông tin user thành công'
        );
    }

    /**
     * Change password
     */
    public function changePassword(ChangePasswordRequest $request): JsonResponse
    {
        $user = $request->user();

        // Check current password
        if (!Hash::check($request->current_password, $user->password)) {
            return $this->errorResponse('Mật khẩu hiện tại không chính xác');
        }

        // Update password
        $user->update([
            'password' => Hash::make($request->new_password),
        ]);

        // Revoke all tokens
        $user->tokens()->delete();

        return $this->successResponse(null, 'Đổi mật khẩu thành công. Vui lòng đăng nhập lại.');
    }

    /**
     * Forgot password - Send OTP
     */
    public function forgotPassword(ForgotPasswordRequest $request): JsonResponse
    {
        $email = strtolower((string) $request->email);
        $user = User::where('email', $email)->first();
        $passwordConfig = config('auth.passwords.users');
        $otpLength = (int) ($passwordConfig['otp_length'] ?? 6);
        $expireMinutes = (int) ($passwordConfig['expire'] ?? 10);

        // Always return the same message to avoid exposing whether email exists.
        if (!$user) {
            return $this->successResponse(null, 'Nếu email tồn tại, hệ thống đã gửi OTP đặt lại mật khẩu.');
        }

        // Create numeric OTP with configured length.
        $min = 10 ** ($otpLength - 1);
        $max = (10 ** $otpLength) - 1;
        $otp = (string) random_int($min, $max);

        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $email],
            [
                'token' => Hash::make($otp),
                'created_at' => now(),
            ]
        );

        Mail::mailer('log')->raw(
            "Yeu cau dat lai mat khau\nEmail: {$email}\nOTP: {$otp}\nHieu luc: {$expireMinutes} phut",
            function ($message) use ($email): void {
                $message->to($email)
                    ->subject('OTP dat lai mat khau');
            }
        );

        return $this->successResponse(null, 'Nếu email tồn tại, hệ thống đã gửi OTP đặt lại mật khẩu.');
    }

    /**
     * Reset password with OTP
     */
    public function resetPassword(ResetPasswordRequest $request): JsonResponse
    {
        $email = strtolower((string) $request->email);
        $passwordConfig = config('auth.passwords.users');
        $expireMinutes = (int) ($passwordConfig['expire'] ?? 10);
        $otpMaxAttempts = (int) ($passwordConfig['otp_max_attempts'] ?? 5);
        $otpLockMinutes = (int) ($passwordConfig['otp_lock_minutes'] ?? 5);
        $invalidOtpMessage = 'OTP không hợp lệ hoặc đã hết hạn.';

        $passwordReset = DB::table('password_reset_tokens')
            ->where('email', $email)
            ->first();

        if (!$passwordReset) {
            return $this->errorResponse($invalidOtpMessage);
        }

        if (now()->diffInMinutes($passwordReset->created_at) > $expireMinutes) {
            DB::table('password_reset_tokens')->where('email', $email)->delete();
            return $this->errorResponse($invalidOtpMessage);
        }

        $failureKey = sprintf('auth:otp_failures:%s|%s', $email, (string) $request->ip());

        if (RateLimiter::tooManyAttempts($failureKey, $otpMaxAttempts)) {
            return $this->errorResponse(
                'Bạn đã nhập sai OTP quá nhiều lần. Vui lòng thử lại sau.',
                429,
                ['retry_after_seconds' => RateLimiter::availableIn($failureKey)]
            );
        }

        if (!Hash::check((string) $request->token, $passwordReset->token)) {
            RateLimiter::hit($failureKey, $otpLockMinutes * 60);

            return $this->errorResponse($invalidOtpMessage);
        }

        RateLimiter::clear($failureKey);

        $user = User::where('email', $email)->first();

        if (!$user) {
            return $this->errorResponse($invalidOtpMessage);
        }

        $user->update([
            'password' => Hash::make($request->password),
        ]);

        DB::table('password_reset_tokens')
            ->where('email', $email)
            ->delete();

        $user->tokens()->delete();

        return $this->successResponse(null, 'Đặt lại mật khẩu thành công. Vui lòng đăng nhập lại.');
    }
}
