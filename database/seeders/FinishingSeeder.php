<?php

namespace Database\Seeders;

use App\Models\Finishing;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class FinishingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Finishing::insert([
            [
                "name" => "Polos/Mentah",
                "code" => "0",
            ],
            [
                "name" => "Veneer",
                "code" => "V",
            ],
            [
                "name" => "Kaca S2",
                "code" => "K2",
            ],
        ]);
    }
}
