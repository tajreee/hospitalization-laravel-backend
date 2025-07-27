<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Cache;

// Import controllers
use App\Http\Controllers\PatientController;
use App\Http\Controllers\NurseController;
use App\Http\Controllers\RoomController;
use App\Http\Controllers\FacilityController;
use App\Http\Controllers\ReservationController;

// Apply throttle globally to all API routes
Route::middleware(['throttle:api'])->group(function () {
    Route::get('/test-redis-socket', function () {
        try {
            // Test basic Redis connection
            $testKey = 'test_socket_connection_' . time();
            $testValue = 'Hello Redis Socket from Laravel!';
            
            // Test Redis facade
            Redis::set($testKey, $testValue);
            $redisValue = Redis::get($testKey);
            Redis::del($testKey);
            
            // Test Cache facade (which should use Redis)
            Cache::put('test_cache_socket', $testValue, 60);
            $cacheValue = Cache::get('test_cache_socket');
            Cache::forget('test_cache_socket');
            
            // Get Redis connection info
            $connectionInfo = Redis::connection()->ping();
            
            return response()->json([
                'success' => true,
                'status' => 200,
                'message' => 'Redis Socket connection successful!',
                'data' => [
                    'redis_connection' => $connectionInfo,
                    'redis_test_value' => $redisValue,
                    'cache_test_value' => $cacheValue,
                    'redis_config' => [
                        'scheme' => config('database.redis.default.scheme'),
                        'path' => config('database.redis.default.path'),
                        'client' => config('database.redis.client'),
                    ]
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'status' => 500,
                'message' => 'Failed to connect to Redis Socket.',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ], 500);
        }
    });
    
    /*
    |--------------------------------------------------------------------------
    | Public Routes (with throttle)
    |--------------------------------------------------------------------------
    */
    Route::post('/auth/login', [App\Http\Controllers\AuthController::class, 'login']);
    Route::post('/auth/logout', [App\Http\Controllers\AuthController::class, 'logout']);
    Route::get('/unauthenticated', [App\Http\Controllers\AuthController::class, 'unauthenticated'])->name('login');
    Route::post('/auth/register', function(Request $request) {
        $role = $request->input('role');
        
        if ($role === 'nurse') {
            return app()->call([NurseController::class, 'store'], $request->all());
        } else {
            return app()->call([PatientController::class, 'store'], $request->all());
        }
    });

    /*
    |--------------------------------------------------------------------------
    | Protected Routes (auth + role + throttle)
    |--------------------------------------------------------------------------
    */
    
    // Patient Routes
    Route::middleware(['auth:api', 'role:patient'])->group(function () {
        Route::get('/reservations', [ReservationController::class, 'reservations']);
        Route::get('/reservations/patient', [ReservationController::class, 'getReservationsByPatient']);
        Route::get('/rooms/{room:id}', [RoomController::class, 'getReservationsBetweenDates']);
        Route::get('/auth/me', [App\Http\Controllers\AuthController::class, 'me']);
        Route::get('/dashboard', [App\Http\Controllers\DashboardController::class, 'patientDashboard']);
    });

    // Nurse Routes
    Route::middleware(['auth:api', 'role:nurse'])->group(function () {
        Route::get('/patients/{patient:nik}', [PatientController::class, 'getPatientByNIK']);
        Route::post('/reservations/create', [ReservationController::class, 'store']);
        Route::get('/reservations/nurse', [ReservationController::class, 'getReservationsByNurse']);
        Route::post('/facilities/create', [FacilityController::class, 'store']);
        Route::get('/facilities', [FacilityController::class, 'facilities']);
        Route::delete('/facilities/{facility:id}/delete', [FacilityController::class, 'deleteFacility']);
        Route::get('/rooms', [RoomController::class, 'rooms']);
        Route::get('/rooms-available', [RoomController::class, 'getAvailableRooms']);
        Route::get('/rooms/{room:id}', [RoomController::class, 'getReservationsBetweenDates']);
        Route::post('/rooms/create', [RoomController::class, 'store']);
        Route::delete('/rooms/{room:id}/delete', [RoomController::class, 'deleteRoom']);
        Route::get('/auth/me', [App\Http\Controllers\AuthController::class, 'me']);
        Route::get('/dashboard', [App\Http\Controllers\DashboardController::class, 'nurseDashboard']);
        
        // Cache management routes
        Route::post('/facilities/cache/refresh', [FacilityController::class, 'refreshCache']);
        Route::get('/facilities/cache/stats', [FacilityController::class, 'cacheStats']);
    });
});