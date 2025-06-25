<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Reservation; // Import Model Reservation
use App\Http\Requests\StoreReservationRequest; // Import Form Request Class yang sudah kita buat
use Illuminate\Support\Facades\DB; // Untuk Database Transactions

class ReservationController extends Controller
{
    public function store(StoreReservationRequest $request){
        try {
            return DB::transaction(function () use ($request) {
                $reservation = Reservation::create([
                    'date_in' => $request->date_in,
                    'date_out' => $request->date_out,
                    'total_fee' => $request->total_fee,
                    'patient_id' => $request->patient_id,
                    'nurse_id' => $request->nurse_id,
                    'room_id' => $request->room_id,
                    'facilities' => $request->facilities,
                ]);

                $reservation->facilities()->attach($request->facilities);

                return response()->json([
                    'success' => true,
                    'status'  => 201,
                    'message' => 'Reservation created successfully.',
                    'reservation' => $reservation->only([
                        'id',
                        'date_in',
                        'date_out',
                        'total_fee',
                        'patient_id',
                        'nurse_id',
                        'room_id',
                        'facilities',
                    ]),
                ], 201);
            });
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'status'  => 500,
                'message' => 'Failed to create reservation.',
                'error'   => $th->getMessage()
            ], 500);
        }
    }
}
