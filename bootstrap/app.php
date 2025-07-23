<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Middleware\HandleCors;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Cache\RateLimiting\Limit;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Tymon\JWTAuth\Exceptions\TokenBlacklistedException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->prepend(HandleCors::class);
        $middleware->alias([
            'role' => \App\Http\Middleware\CheckRole::class,
        ]);

        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(20)->by($request->user()?->id ?: $request->ip());
                // ->response(function (Request $request, array $headers) {
                //     return response()->json([
                //         'success' => false,
                //         'status' => 429,
                //         'message' => 'Too many requests. Please slow down.',
                //         'retry_after' => $headers['Retry-After'] ?? 60
                //     ], 429);
                // });
        });
    })

    ->withExceptions(function (Exceptions $exceptions): void {
        // Handle validation exceptions and return JSON response
        $exceptions->renderable(function (ValidationException $exception, Request $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'status'  => 422,
                    'message' => 'The given data was invalid.',
                    'errors' => $exception->errors(),
                ], 422);
            }
        });

        // Handle model not found exceptions and return JSON response
        $exceptions->renderable(function (ModelNotFoundException $exception, Request $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'status'  => 404,
                    'message' => 'Resource not found.',
                ], 404);
            }
        });

        // Handle JWT Token Expired Exception
        $exceptions->renderable(function (TokenExpiredException $exception, Request $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'status'  => 401,
                    'message' => 'Token has expired. Please login again.',
                    'error_code' => 'TOKEN_EXPIRED'
                ], 401);
            }
        });

        // Handle JWT Token Invalid Exception
        $exceptions->renderable(function (TokenInvalidException $exception, Request $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'status'  => 401,
                    'message' => 'Token is invalid. Please login again.',
                    'error_code' => 'TOKEN_INVALID'
                ], 401);
            }
        });

        // Handle JWT Token Blacklisted Exception
        $exceptions->renderable(function (TokenBlacklistedException $exception, Request $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'status'  => 401,
                    'message' => 'Token has been blacklisted. Please login again.',
                    'error_code' => 'TOKEN_BLACKLISTED'
                ], 401);
            }
        });

        // Handle General JWT Exception
        $exceptions->renderable(function (JWTException $exception, Request $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'status'  => 401,
                    'message' => 'Token not provided or invalid. Please login first.',
                    'error_code' => 'TOKEN_ABSENT'
                ], 401);
            }
        });

        // Handle Unauthenticated Exception (when middleware auth fails)
        $exceptions->renderable(function (\Illuminate\Auth\AuthenticationException $exception, Request $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'status'  => 401,
                    'message' => 'Unauthenticated. Please login first.',
                    'error_code' => 'UNAUTHENTICATED'
                ], 401);
            }
        });

        // Handle Authorization Exception (when user doesn't have permission)
        $exceptions->renderable(function (\Illuminate\Auth\Access\AuthorizationException $exception, Request $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'status'  => 403,
                    'message' => 'This action is unauthorized.',
                    'error_code' => 'FORBIDDEN'
                ], 403);
            }
        });

        $exceptions->renderable(function (\Illuminate\Http\Exceptions\ThrottleRequestsException $exception, Request $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'status'  => 429,
                    'message' => 'Too many requests. Please slow down.',
                    'error_code' => 'TOO_MANY_REQUESTS'
                ], 429);
            }
        });
    })
    ->create();
