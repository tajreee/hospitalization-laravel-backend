<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\StorePatientRequest;
use App\Http\Requests\StoreNurseRequest;
use App\Models\User;
use App\Models\Patient;
use App\Models\Nurse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        if (!$token = auth('api')->attempt($credentials)) {
            return response()->json([
                'success' => false,
                'status' => 401,
                'message' => 'Invalid credentials'
            ], 401);
        }

        return response()->json([
            'success' => true,
            'status'  => 200,
            'message' => 'Login successful',
            'token'   => $token,
            'token_type' => 'bearer',
            'expires_in' => auth('api')->factory()->getTTL() * 60,
            'user'    => auth('api')->user()->only(['id', 'name', 'email', 'role']),
        ]);
    }

    public function me()
    {
        // JWT exceptions akan ditangani secara otomatis oleh global exception handler
        return response()->json([
            'success' => true,
            'status'  => 200,
            'message' => 'User data retrieved successfully',
            'data'    => [
                'user' => auth('api')->user()->only(['id', 'name', 'email', 'role']),
            ],
        ]);
    }

    public function logout()
    {
        // JWT exceptions akan ditangani secara otomatis oleh global exception handler
        auth('api')->logout();
        
        return response()->json([
            'success' => true,
            'status'  => 200,
            'message' => 'Successfully logged out'
        ]);
    }

    public function refresh()
    {
        // JWT exceptions akan ditangani secara otomatis oleh global exception handler
        $newToken = auth('api')->refresh();

        return response()->json([
            'success' => true,
            'status'  => 200,
            'message' => 'Token refreshed successfully',
            'token'   => $newToken,
            'token_type' => 'bearer',
            'expires_in' => auth('api')->factory()->getTTL() * 60,
        ]);
    }

    public function unauthenticated()
    {
        return response()->json([
            "success" => false,
            "status" => 401,
            "message" => "Unauthenticated. Please login first",
        ], 401);
    }
}
