<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

use App\Http\Controllers\PatientController; // Pastikan ini ada
use App\Http\Controllers\NurseController; // Pastikan ini ada
use App\Http\Controllers\RoomController; // Pastikan ini ada
use App\Http\Controllers\FacilityController; // Pastikan ini ada
use App\Http\Controllers\ReservationController; // Pastikan ini ada

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Public Routes
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

// Patient Routes
Route::middleware(['auth:api', 'role:patient'])->group(function () {
    Route::get('/reservations', [ReservationController::class, 'reservations']);
    Route::get('/reservations/patient', [ReservationController::class, 'getReservationsByPatient']);
    Route::get('/rooms/{room:id}', [RoomController::class, 'getReservationsBetweenDates']);
    Route::get('/auth/me', [App\Http\Controllers\AuthController::class, 'me']);
    Route::get('/dashboard', [App\Http\Controllers\DashboardController::class, 'patientDashboard']);
    Route::get('/rooms/{room:id}', [RoomController::class, 'getReservationBetweenDates']);
});

// Nurse Routes
Route::middleware(['auth:api', 'role:nurse'])->group(function () {
    Route::get('/patients/{patient:nik}', [PatientController::class, 'getPatientByNIK']);
    Route::post('/reservations', [ReservationController::class, 'store']);
    Route::get('/reservations/nurse', [ReservationController::class, 'getReservationsByNurse']);
    Route::post('/facilities/create', [FacilityController::class, 'store']);
    Route::get('/facilities', [FacilityController::class, 'facilities']);
    Route::delete('/facilities/{facility:id}/delete', [FacilityController::class, 'deleteFacility']);
    Route::get('/rooms', [RoomController::class, 'rooms']);
    Route::get('/rooms/available', [RoomController::class, 'getAvailableRooms']);
    Route::post('/rooms/create', [RoomController::class, 'store']);
    // Route::get('/rooms/{room:id}', [RoomController::class, 'getRoomById']);
    Route::get('/rooms/{room:id}', [RoomController::class, 'getReservationBetweenDates']);
    Route::delete('/rooms/{room:id}/delete', [RoomController::class, 'deleteRoom']);
    Route::get('/auth/me', [App\Http\Controllers\AuthController::class, 'me']);
    Route::get('/dashboard', [App\Http\Controllers\DashboardController::class, 'nurseDashboard']);

});
// ... rute-rute lain jika ada ...