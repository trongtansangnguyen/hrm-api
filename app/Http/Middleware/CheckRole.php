<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 401);
        }

        // Map role names to role values
        $roleMap = [
            'admin' => 1,
            'manager' => 2,
            'employee' => 3,
        ];

        // Check if user has one of the required roles
        foreach ($roles as $role) {
            $roleValue = $roleMap[strtolower($role)] ?? null;
            if ($roleValue && $user->role->value === $roleValue) {
                return $next($request);
            }
        }

        return response()->json([
            'success' => false,
            'message' => 'Bạn không có quyền truy cập',
        ], 403);
    }
}
