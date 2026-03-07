<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;

class EmailIpRateLimit
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, string $action = 'forgot'): mixed
    {
        $email = strtolower((string) $request->input('email', 'no-email'));
        $ip = (string) $request->ip();
        $config = config('auth.passwords.users');

        $maxAttempts = $action === 'reset'
            ? (int) ($config['reset_max_attempts'] ?? 10)
            : (int) ($config['forgot_max_attempts'] ?? 3);

        $decaySeconds = (int) ($config['throttle'] ?? 60);
        $key = sprintf('auth:%s:%s|%s', $action, $email, $ip);

        if (RateLimiter::tooManyAttempts($key, $maxAttempts)) {
            $retryAfter = RateLimiter::availableIn($key);

            return response()->json([
                'success' => false,
                'message' => 'Too many requests. Please try again later.',
                'retry_after_seconds' => $retryAfter,
            ], 429);
        }

        RateLimiter::hit($key, $decaySeconds);

        return $next($request);
    }
}
