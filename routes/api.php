<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\LeaveRequestController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Public routes    
Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/test-error', function () {
        throw new \RuntimeException('Test exception from auth route group');
    });
    Route::post('/forgot-password', [AuthController::class, 'forgotPassword'])
        ->middleware('email_ip_rate_limit:forgot');
    Route::post('/reset-password', [AuthController::class, 'resetPassword'])
        ->middleware('email_ip_rate_limit:reset');
});

Route::get('/debug/my-ip', function (Request $request) {
    $forwardedFor = $request->header('X-Forwarded-For');

    return response()->json([
        'success' => true,
        'message' => 'Current request IP',
        'data' => [
            'ip' => $request->ip(),
            'ips' => $request->ips(),
            'remote_addr' => $request->server('REMOTE_ADDR'),
            'forwarded_client_ip' => $forwardedFor ? trim(explode(',', $forwardedFor)[0]) : null,
            'headers' => [
                'x_forwarded_for' => $forwardedFor,
                'x_real_ip' => $request->header('X-Real-IP'),
                'forwarded' => $request->header('Forwarded'),
            ],
        ],
    ]);
});

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    // Auth routes
    Route::prefix('auth')->group(function () {
        Route::get('/me', [AuthController::class, 'me']);
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::post('/change-password', [AuthController::class, 'changePassword']);
    });

    Route::prefix('attendance')->group(function () {
        Route::get('/today', [AttendanceController::class, 'today']);
        Route::post('/check-in', [AttendanceController::class, 'checkIn']);
        Route::post('/check-out', [AttendanceController::class, 'checkOut']);
    });
});

Route::get('/employees/count', [EmployeeController::class, 'count'])->name('employees.count');

Route::middleware(['auth:sanctum', 'role:admin,manager'])->group(function () {
    Route::prefix('employees')->group(function () {
       Route::get('/', [EmployeeController::class, 'index']);
       Route::get('/{employee}', [EmployeeController::class, 'show']);
       Route::post('/', [EmployeeController::class, 'store']);
       Route::put('/{employee}', [EmployeeController::class, 'update']);
       Route::delete('/{employee}', [EmployeeController::class, 'destroy']);
    });
});


Route::middleware('auth:sanctum')->group(function () {

    Route::apiResource('leave-requests', LeaveRequestController::class);

    // Chỉ Manager mới được phê duyệt và từ chối
    Route::middleware('role:manager')->group(function () {
        Route::post('leave-requests/{leaveRequest}/approve', [LeaveRequestController::class, 'approve'])
             ->name('leave-requests.approve');

        Route::post('leave-requests/{leaveRequest}/reject', [LeaveRequestController::class, 'reject'])
             ->name('leave-requests.reject');
    });
});