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
                    'data' => [
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
        $nurseId = auth('api')->user()->nurse->id;

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

    public function getReservationsBetweenDates(Request $request) {
        $dateIn = $request->input('dateIn');
        $dateOut = $request->input('dateOut');
        $roomId = $request->input('roomId');

        // Validate required parameters
        if (!$dateIn || !$dateOut || !$roomId) {
            return response()->json([
                'success' => false,
                'status'  => 400,
                'message' => 'Check-in date, check-out date, and room ID are required.',
            ], 400);
        }

        try {
            $checkInDate = \Carbon\Carbon::parse($dateIn);
            $checkOutDate = \Carbon\Carbon::parse($dateOut);
            
            if ($checkOutDate->lte($checkInDate)) {
                return response()->json([
                    'success' => false,
                    'status'  => 400,
                    'message' => 'Check-out date must be after check-in date.',
                ], 400);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'status'  => 400,
                'message' => 'Invalid date format. Please use YYYY-MM-DD format.',
            ], 400);
        }

        try {
            // Find reservations that overlap with the given date range
            // A reservation overlaps if:
            // 1. It starts before our end date AND
            // 2. It ends after our start date
            $reservations = Reservation::with([
                'patient', 
                'patient.user',
                'nurse', 
                'room', 
                'facilities'
            ])
                ->where('room_id', $roomId)
                ->where(function($query) use ($dateIn, $dateOut) {
                    $query->where('date_in', '<', $dateOut)
                          ->where('date_out', '>', $dateIn);
                })
                ->latest()
                ->paginate($request->per_page ?? 10);

            // Calculate summary statistics
            $totalReservations = $reservations->total();
            $currentPageReservations = $reservations->count();

            return response()->json([
                'success' => true,
                'status'  => 200,
                'message' => 'Reservations between dates retrieved successfully.',
                'data'    => [
                    'reservations' => $reservations,
                    'search_criteria' => [
                        'room_id' => $roomId,
                        'date_in' => $dateIn,
                        'date_out' => $dateOut,
                    ],
                    'summary' => [
                        'total_reservations' => $totalReservations,
                        'current_page_count' => $currentPageReservations,
                        'date_range_days' => $checkInDate->diffInDays($checkOutDate),
                    ]
                ],
            ], 200);

        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'status'  => 500,
                'message' => 'Failed to retrieve reservations.',
                'error'   => $th->getMessage()
            ], 500);
        }
    }
}
