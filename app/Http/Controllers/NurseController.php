<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User; // Import Model User
use App\Models\Nurse; // Import Model Nurse
use App\Http\Requests\StoreNurseRequest; // Import Form Request Class yang sudah kita buat
use Illuminate\Support\Facades\Hash; // Untuk hashing password
use Illuminate\Support\Facades\DB; // Untuk Database Transactions

class NurseController extends Controller
{
    public function store(StoreNurseRequest $request) {
        try {
            return DB::transaction(function () use ($request) {
                $user = User::create([
                    'name' => $request->name,
                    'email' => $request->email,
                    'password' => Hash::make($request->password),
                ]);

                $nurse = $user->nurse()->create();

                return response()->json([
                    'success' => true,
                    'status'  => 201,
                    'message' => 'User and Nurse created successfully.',
                    'user'    => $user->only(['id', 'name', 'email']),
                    'nurse'   => $nurse->only(['user_id']),
                ], 201);
            });
        } catch (\Throwable $th) {
            throw $th;
        }
    }
}
