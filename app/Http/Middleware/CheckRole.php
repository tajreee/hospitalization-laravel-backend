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
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        try {
            $user = auth('api')->user();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'status'  => 401,
                    'message' => 'Unauthorized. Please login to continue.',
                ], 401);
            }

            // Check if user has one of the required roles
            if (in_array($user->role, $roles)) {
                return $next($request);
            }

            return response()->json([
                'success' => false,
                'status'  => 403,
                'message' => 'Forbidden. You do not have permission to access this resource.',
            ], 403);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false, 
                'status'  => 500,
                'message' => 'An error occurred while checking permissions.',
                'error'   => $th->getMessage()
            ], 500);
        }
    }
}
