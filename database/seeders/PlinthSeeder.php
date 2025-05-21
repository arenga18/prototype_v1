<?php

namespace Database\Seeders;

use App\Models\Plinth;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PlinthSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Plinth::insert([
            [
                "name" => "HPL 50",
                "code" => "h50",
            ],
            [
                "name" => "MPP 01",
                "code" => "a",
            ],
            [
                "name" => "MPP 04 + Magnet (Gas)",
                "code" => "b",
            ],
        ]);
    }
}
