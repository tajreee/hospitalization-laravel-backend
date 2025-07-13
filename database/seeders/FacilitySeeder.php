<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class FacilitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach (range(1, 10) as $i) {
            \App\Models\Facility::create([
                'name' => fake()->word(),
                'fee' => rand(10000, 50000),
            ]);
        }
        // --- IGNORE ---
    }
}
