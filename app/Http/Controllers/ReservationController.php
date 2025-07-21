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
                // Create reservation without total_fee first
                $reservation = Reservation::create([
                    'date_in' => $request->date_in,
                    'date_out' => $request->date_out,
                    'patient_id' => $request->patient_id,
                    'nurse_id' => $request->nurse_id,
                    'room_id' => $request->room_id,
                    'total_fee' => 0, // Will be calculated later
                ]);

                // Attach facilities
                $reservation->facilities()->attach($request->facilities);

                // Calculate and update total fee
                $reservation->updateTotalFee($request->facilities);

                // Load relationships for response
                $reservation->load(['patient', 'patient.user', 'nurse', 'room', 'facilities']);

                return response()->json([
                    'success' => true,
                    'status'  => 201,
                    'message' => 'Reservation created successfully.',
                    'data' => [
                        'reservation' => $reservation,
                        'cost_breakdown' => [
                            'number_of_days' => $reservation->number_of_days,
                            'room_cost' => $reservation->room_cost,
                            'facilities_cost' => $reservation->facilities_cost,
                            'total_fee' => $reservation->total_fee,
                        ]
                    ]
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

    public function reservations(Request $request)
    {
        $reservations = Reservation::with([
            'patient', 
            'patient.user', // Include the user relationship from patient
            'nurse', 
            'room', 
            'facilities'
        ])
            ->latest()
            ->paginate($request->per_page ?? 10);

        return response()->json([
            'success' => true,
            'status'  => 200,
            'message' => 'Reservations retrieved successfully.',
            'data'    => $reservations,
        ], 200);
    }

    public function getReservationsByNurse(Request $request) {
        $nurseId = auth('api')->user()->nurse->user_id;

        $reservations = Reservation::with([
            'patient', 
            'patient.user', // Include the user relationship from patient
            'nurse', 
            'room', 
            'facilities'
        ])
            ->where('nurse_id', $nurseId)
            ->latest()
            ->paginate($request->per_page ?? 10);
        
        return response()->json([
            'success' => true,
            'status'  => 200,
            'message' => 'Reservations for nurse retrieved successfully.',
            'data'    => [
                'reservations'    => $reservations
            ],
        ]);
    }

    public function getReservationsByPatient(Request $request) {
        $patientId = auth('api')->user()->patient->id;

        $reservations = Reservation::with([
            'patient', 
            'patient.user', // Include the user relationship from patient
            'nurse', 
            'room', 
            'facilities'
        ])
            ->where('patient_id', $patientId)
            ->latest()
            ->paginate($request->per_page ?? 10);
        
        return response()->json([
            'success' => true,
            'status'  => 200,
            'message' => 'Reservations for patient retrieved successfully.',
            'data'    => [
                'reservations'    => $reservations
            ],
        ]);
    }
}
