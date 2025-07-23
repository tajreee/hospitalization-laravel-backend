<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Redis;

// Import controllers
use App\Http\Controllers\PatientController;
use App\Http\Controllers\NurseController;
use App\Http\Controllers\RoomController;
use App\Http\Controllers\FacilityController;
use App\Http\Controllers\ReservationController;

// Apply throttle globally to all API routes
Route::middleware(['throttle:api'])->group(function () {
    Route::get('/test-redis', function () {
    try {
        // Coba tulis sesuatu ke Redis
        Redis::set('test_koneksi', 'Halo Redis dari Laravel!');

        // Coba baca kembali
        $value = Redis::get('test_koneksi');

        // Hapus kunci setelah tes
        Redis::del('test_koneksi');

        return response()->json([
            'success' => true,
            'status'  => 200,
            'message' => 'Koneksi Redis Berhasil!', 'value' => $value
        ], 200);

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'status'  => 500,
            'message' => 'Gagal terhubung ke Redis.',
            'error'   => $e->getMessage()
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
    });
});