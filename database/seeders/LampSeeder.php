<?php

namespace Database\Seeders;

use App\Models\Lamp;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class LampSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Lamp::insert([
            [
                "name" => "M-led-06",
                "code" => "6",
            ],
            [
                "name" => "M-led-10",
                "code" => "10",
            ],
            [
                "name" => "M-led-04",
                "code" => "4",
            ],
        ]);
    }
}
