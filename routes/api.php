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

// Patient routes
Route::prefix('patients')->group(function () {
    Route::post('/create', [PatientController::class, 'store']);
    Route::get('/{patient:nik}', [PatientController::class, 'getPatientByNIK']);
});

// Nurse routes
Route::prefix('nurses')->group(function () {
    Route::post('/create', [NurseController::class, 'store']);
});

// Room routes
Route::prefix('rooms')->group(function () {
    Route::post('/create', [RoomController::class, 'store']);
});

// Facility routes
Route::prefix('facilities')->group(function () {
    Route::post('/create', [FacilityController::class, 'store']);
    Route::get('/', [FacilityController::class, 'facilities']);
    Route::delete('/{facility:id}/delete', [FacilityController::class, 'deleteFacility']);
});

// Reservation routes
Route::prefix('reservations')->group(function () {
    Route::post('/create', [ReservationController::class, 'store']);
    Route::get('/', [ReservationController::class, 'reservations']);
});

// ... rute-rute lain jika ada ...