<?php

namespace App\Http\Controllers;

use App\Models\User; // Import Model User
use App\Models\Patient; // Import Model Patient
use App\Http\Requests\StorePatientRequest; // Import Form Request Class yang sudah kita buat
use Illuminate\Support\Facades\Hash; // Untuk hashing password
use Illuminate\Support\Facades\DB; // Untuk Database Transactions

class PatientController extends Controller
{
    public function store(StorePatientRequest $request) {
        try {
            return DB::transaction(function () use ($request) {
                $user = User::create([
                    'name' => $request->name,
                    'email' => $request->email,
                    'password' => Hash::make($request->password),
                ]);

                $patient = $user->patient()->create([
                    'nik' => $request->nik,
                    'birth_date' => $request->birth_date,
                ]);

                return response()->json([
                    'message' => 'User dan Patient berhasil dibuat.',
                    'user'    => $user->only(['id', 'name', 'email', 'gender']),
                    'patient' => $patient->only(['user_id', 'nik', 'birth_date']),
                ], 201);

            });
        } catch (\Throwable $th) {
            throw $th;
        }
    }
}
