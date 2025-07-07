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
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        return response()->json([
            'success' => true,
            'status'  => 200,
            'message' => 'Login successful',
            'token'   => $token,
            'user'    => auth('api')->user()->only(['id', 'name', 'email', 'role']),
        ]);
    }

    public function me()
    {
        return response()->json([
            'success' => true,
            'status'  => 200,
            'message' => 'User data retrieved successfully',
            'data'    => [
                "user" => auth('api')->user()->only(['id', 'name', 'email', 'role']),
            ],
        ]);
    }

    public function logout()
    {
        auth('api')->logout();
        return response()->json(['message' => 'Successfully logged out']);
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
