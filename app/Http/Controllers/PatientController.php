<?php

namespace App\Http\Controllers;

use App\Models\User; // Import Model User
use App\Models\Patient; // Import Model Patient
use App\Http\Requests\StorePatientRequest; // Import Form Request Class yang sudah kita buat
use Illuminate\Support\Facades\Hash; // Untuk hashing password
use Illuminate\Support\Facades\DB; // Untuk Database Transactions

class PatientController extends Controller
{
    public static function store(StorePatientRequest $request) {
        try {
            return DB::transaction(function () use ($request) {
                $user = User::create([
                    'name' => $request->name,
                    'email' => $request->email,
                    'password' => Hash::make($request->password),
                    'role' => 'patient', // Set role to 'patient'
                ]);

                $patient = $user->patient()->create([
                    'nik' => $request->nik,
                    'birth_date' => $request->birth_date,
                ]);

                return response()->json([
                    'success' => true,
                    'status'  => 201,
                    'message' => 'User dan Patient berhasil dibuat.',
                    'user'    => $user->only(['id', 'name', 'email', 'gender', 'role']),
                    'patient' => $patient->only(['user_id', 'nik', 'birth_date']),
                ], 201);

            });
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function getPatientByNIK(Patient $patient) {
        $patient->load('user');
        
        return response()->json([
            'success' => true,
            'status'  => 200,
            'message' => 'Data pasien berhasil ditemukan.',
            'patient' => [
                'user_id' => $patient->user_id,
                'name' => $patient->user->name,
                'email' => $patient->user->email,
                'gender' => $patient->user->gender,
                'nik' => $patient->nik,
                'birth_date' => $patient->birth_date,
            ],
        ], 200);
    }
}
