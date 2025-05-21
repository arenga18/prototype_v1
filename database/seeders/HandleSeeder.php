<?php

namespace Database\Seeders;

use App\Models\Handle;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class HandleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Handle::insert([
            [
                "name" => "MH 02",
                "code" => "2",
            ],
            [
                "name" => "M-PRF-01+MH02 coak",
                "code" => "P2c",
            ],
            [
                "name" => "MH 03B",
                "code" => "3b",
            ],
        ]);
    }
}
