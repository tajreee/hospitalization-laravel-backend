<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\StoreFacilityRequest;
use App\Models\Facility;
use Illuminate\Support\Facades\DB;

class FacilityController extends Controller
{
    public function store(StoreFacilityRequest $request) {
        try {
            return DB::transaction(function () use ($request) {
                $facility = Facility::create([
                    'name' => $request->name,
                    'fee'  => $request->fee,
                ]);

                return response()->json([
                    'success' => true,
                    'status'  => 201,
                    'message' => 'Facility created successfully.',
                    'facility' => $facility->only(['id', 'name', 'fee']),
                ], 201);
            });
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'status'  => 500,
                'message' => 'Failed to create facility.',
                'error'   => $th->getMessage()
            ], 500);
        }
    }

    public function facilities(Request $request)
    {
        $facilities = Facility::all();

        return response()->json([
            'success' => true,
            'status'  => 200,
            'message' => 'Facilities retrieved successfully.',
            'facilities' => $facilities->map(function ($facility) {
                return $facility->only(['id', 'name', 'fee']);
            }),
        ], 200);
    }
}
