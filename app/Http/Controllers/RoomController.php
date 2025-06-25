<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Room; // Import Model Room
use App\Http\Requests\StoreRoomRequest; // Import Form Request Class yang sudah kita buat
use Illuminate\Support\Facades\DB; // Untuk Database Transactions

class RoomController extends Controller
{
    public function store(StoreRoomRequest $request) {
        try {
            return DB::transaction(function () use ($request) {
                $room = Room::create([
                    'name' => $request->name,
                    'description' => $request->description,
                    'max_capacity' => $request->max_capacity,
                    'price_per_day' => $request->price_per_day,
                ]);

                return response()->json([
                    'success' => true,
                    'status'  => 201,
                    'message' => 'Room created successfully.',
                    'room'    => $room->only(['id', 'name', 'description', 'max_capacity', 'price_per_day']),
                ], 201);
            });
        } catch (\Throwable $th) {
            return response()->json([
            'success' => false,
            'status'  => 500,
            'message' => 'Failed to create room.',
            'error'   => $th->getMessage()
            ], 500);
        }
    }
}
