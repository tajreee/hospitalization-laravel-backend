<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        //
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
        $exceptions->renderable(function (\Symfony\Component\HttpKernel\Exception\NotFoundHttpException $exception, Request $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                // Check if the previous exception was ModelNotFoundException
                if ($exception->getPrevious() instanceof ModelNotFoundException) {
                    return response()->json([
                    'success' => false,
                    'status'  => 404,
                    'message' => 'Data not found.',
                    ], 404);
                }
            
            // Handle generic 404 errors
            return response()->json([
                'success' => false,
                'status'  => 404,
                'message' => 'Not found.',
            ], 404);
            }
        });
        
    })->create();
