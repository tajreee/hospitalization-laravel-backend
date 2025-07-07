<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function nurseDashboard() {
        $totalReservations = \App\Models\Reservation::all()->count();
        $totalRooms = \App\Models\Room::all()->count();
        $totalPatients = \App\Models\Patient::all()->count();
        
        return response()->json([
            'success' => true,
            'status'  => 200,
            'message' => 'Dashboard data retrieved successfully',
            'data'    => [
                'total_reservations' => $totalReservations,
                'total_rooms'        => $totalRooms,
                'total_patients'     => $totalPatients,
            ],
        ]);
    }

    public function patientDashboard() {
        $user = auth('api')->user();
        $totalReservations = \App\Models\Reservation::where('patient_id', $user->id)->get()->count();

        
        return response()->json([
            'success' => true,
            'status'  => 200,
            'message' => 'Dashboard data retrieved successfully',
            'data'    => [
                'total_reservations' => $totalReservations,
            ],
        ]);
    }
}
