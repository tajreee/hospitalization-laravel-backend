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
                    'data'    => [
                        'room'    => $room->only(['id', 'name', 'description', 'max_capacity', 'price_per_day']),
                    ],
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

    public function rooms() {
        $rooms = Room::all();

        return response()->json([
            'success' => true,
            'status'  => 200,
            'message' => 'Rooms retrieved successfully.',
            'data'    => [
                'rooms'   => $rooms->map(function ($room) {
                    return $room->only(['id', 'name', 'description', 'max_capacity', 'price_per_day']);
                }),
            ],
        ], 200);
    }

    public function getAvailableRooms(Request $request) {
        $dateIn = $request->input('dateIn');
        $dateOut = $request->input('dateOut');

        // Validate required parameters
        if (!$dateIn || !$dateOut) {
            return response()->json([
                'success' => false,
                'status'  => 400,
                'message' => 'Check-in and check-out dates are required.',
            ], 400);
        }

        // Validate date format and logic
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
            // Get all rooms with their current reservation count for the specified date range
            $availableRooms = Room::select('room.*')
                ->selectRaw('COALESCE(COUNT(reservation.id), 0) as current_reservations')
                ->leftJoin('reservation', function($join) use ($dateIn, $dateOut) {
                    $join->on('room.id', '=', 'reservation.room_id')
                        ->where(function($query) use ($dateIn, $dateOut) {
                            // Find overlapping reservations - a reservation overlaps if:
                            // 1. It starts before our checkout date AND
                            // 2. It ends after our checkin date
                            $query->where('reservation.date_in', '<', $dateOut)
                                  ->where('reservation.date_out', '>', $dateIn);
                        })
                        ->whereNull('reservation.deleted_at'); // Only count non-deleted reservations
                })
                ->whereNull('room.deleted_at') // Only include non-deleted rooms
                ->groupBy('room.id', 'room.name', 'room.description', 'room.max_capacity', 'room.price_per_day', 'room.created_at', 'room.updated_at', 'room.deleted_at')
                ->havingRaw('COALESCE(COUNT(reservation.id), 0) < room.max_capacity')
                ->orderBy('room.name')
                ->get();

            // Add availability information to each room
            $roomsWithAvailability = $availableRooms->map(function ($room) {
                return [
                    'id' => $room->id,
                    'name' => $room->name,
                    'description' => $room->description,
                    'max_capacity' => $room->max_capacity,
                    'price_per_day' => $room->price_per_day,
                    'current_reservations' => $room->current_reservations,
                    'available_capacity' => $room->max_capacity - $room->current_reservations,
                ];
            });

            return response()->json([
                'success' => true,
                'status'  => 200,
                'message' => 'Available rooms retrieved successfully.',
                'data'    => [
                    'rooms' => $roomsWithAvailability,
                    'search_criteria' => [
                        'date_in' => $dateIn,
                        'date_out' => $dateOut,
                    ],
                    'total_available_rooms' => $roomsWithAvailability->count(),
                ],
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'status'  => 500,
                'message' => 'Failed to retrieve available rooms.',
                'error'   => $th->getMessage()
            ], 500);
        }

        // Alternative approach using the existing methods in the Room model
        // try {
        //     $availableRooms = Room::all()->filter(function ($room) use ($dateIn, $dateOut) {
        //         return $room->isAvailable($dateIn, $dateOut);
        //     });

        //     $roomsData = $availableRooms->map(function ($room) use ($dateIn, $dateOut) {
        //         $currentReservations = $room->getOverlappingReservations($dateIn, $dateOut);
        //         return [
        //             'id' => $room->id,
        //             'name' => $room->name,
        //             'description' => $room->description,
        //             'max_capacity' => $room->max_capacity,
        //             'price_per_day' => $room->price_per_day,
        //             'current_reservations' => $currentReservations,
        //             'available_capacity' => $room->max_capacity - $currentReservations,
        //         ];
        //     });

        //     return response()->json([
        //         'success' => true,
        //         'status'  => 200,
        //         'message' => 'Available rooms retrieved successfully.',
        //         'data'    => [
        //             'rooms' => $roomsData->values(),
        //             'search_criteria' => [
        //                 'date_in' => $dateIn,
        //                 'date_out' => $dateOut,
        //             ],
        //             'total_available_rooms' => $roomsData->count(),
        //         ],
        //     ], 200);
        // } catch (\Throwable $th) {
        //     return response()->json([
        //         'success' => false,
        //         'status'  => 500,
        //         'message' => 'Failed to retrieve available rooms.',
        //         'error'   => $th->getMessage()
        //     ], 500);
        // }
    }

    public function getRoomById(Room $room) {
        return response()->json([
            'success' => true,
            'status'  => 200,
            'message' => 'Room retrieved successfully.',
            'data'    => [
                'room'    => $room->only(['id', 'name', 'description', 'max_capacity', 'price_per_day']),
            ],
        ], 200);
    }

    public function getReservationsBetweenDates(Room $room, Request $request) {
        $dateIn = $request->input('dateIn');
        $dateOut = $request->input('dateOut');

        // Validate required parameters
        if (!$dateIn) {
            $dateIn = now()->startOfDay()->toDateString();
        }

        if (!$dateOut) {
            $dateOut = \Carbon\Carbon::parse($dateIn)->addDay()->toDateString();
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
                ->where('room_id', $room->id)
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

    public function deleteRoom(Room $room) {
        try {
            return DB::transaction(function () use ($room) {
                $room->delete();

                return response()->json([
                    'success' => true,
                    'status'  => 200,
                    'message' => 'Room deleted successfully.',
                ], 200);
            });
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'status'  => 500,
                'message' => 'Failed to delete room.',
                'error'   => $th->getMessage()
            ], 500);
        }
    }
}
