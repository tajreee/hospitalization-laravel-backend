<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Facility;

class FacilitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create facilities with predefined realistic data
        $facilities = [
            ['name' => 'Wi-Fi', 'fee' => 15000],
            ['name' => 'Air Conditioning', 'fee' => 25000],
            ['name' => 'Television', 'fee' => 10000],
            ['name' => 'Telephone', 'fee' => 5000],
            ['name' => 'Mini Fridge', 'fee' => 20000],
            ['name' => 'Private Bathroom', 'fee' => 30000],
            ['name' => 'Room Service', 'fee' => 35000],
            ['name' => 'Laundry Service', 'fee' => 12000],
            ['name' => 'Medical Equipment', 'fee' => 45000],
            ['name' => 'Wheelchair Access', 'fee' => 8000],
        ];

        foreach ($facilities as $facility) {
            Facility::create($facility);
        }
    }
}
