<?php

namespace Database\Seeders;

use App\Models\LayerPosition;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class LayerPositionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        LayerPosition::insert([
            [
                "name" => "Lapis Kanan Kiri bawah",
                "code" => "►▲◄",
            ],
            [
                "name" => "Lapis Kiri sebagian kanan full",
                "code" => "→◄",
            ],
            [
                "name" => "Lapis atas+kanan sebagian",
                "code" => "▼←",
            ],
        ]);
    }
}
