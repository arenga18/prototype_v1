<?php

namespace Database\Seeders;

use App\Models\ClosingSystem;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ClosingSystemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        ClosingSystem::insert([
            [
                "name" => "Swing D 45°",
                "code" => "sw45",
            ],
            [
                "name" => "Swing D 165°",
                "code" => "SW",
            ],
            [
                "name" => "Pocket Door Knockers PD-066",
                "code" => "PD-066",
            ],
        ]);
    }
}
