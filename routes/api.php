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

// Rute untuk registrasi pasien baru
Route::post('/patients/create', [PatientController::class, 'store']);
Route::post('/nurses/create', [NurseController::class, 'store']);
Route::post('/rooms/create', [RoomController::class, 'store']);
Route::post('/facilities/create', [FacilityController::class, 'store']);
Route::post('/reservations/create', [ReservationController::class, 'store']);

// ... rute-rute lain jika ada ...