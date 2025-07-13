<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RoomSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach (range(1, 10) as $i) {
            \App\Models\Room::create([
                'name' => 'Room ' . $i,
                'description' => 'Description for Room ' . $i,
                'max_capacity' => rand(1, 6),
                'price_per_day' => rand(100000, 500000),
            ]);
        }
    }
}
