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
    public static function store(StoreNurseRequest $request) {
        try {
            return DB::transaction(function () use ($request) {
                $user = User::create([
                    'name' => $request->name,
                    'email' => $request->email,
                    'password' => Hash::make($request->password),
                    'role' => 'nurse', // Set role to 'nurse'
                ]);

                $nurse = $user->nurse()->create();

                return response()->json([
                    'success' => true,
                    'status'  => 201,
                    'message' => 'User and Nurse created successfully.',
                    'user'    => $user->only(['id', 'name', 'email', 'role']),
                    'nurse'   => $nurse->only(['user_id']),
                ], 201);
            });
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function getAllNurses(Request $request)
    {
        $nurses = Nurse::with('user')->get();

        return response()->json([
            'success' => true,
            'status'  => 200,
            'message' => 'List of all nurses.',
            'data'    => $nurses->map(function ($nurse) {
                return [
                    'id'    => $nurse->id,
                    'name'  => $nurse->user->name,
                    'email' => $nurse->user->email,
                    'role'  => $nurse->user->role,
                ];
            }),
        ], 200);
    }
}
