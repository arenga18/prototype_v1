<?php

namespace Database\Seeders;

use App\Models\BoxCarcaseShape;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class BoxCarcaseShapeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        BoxCarcaseShape::insert([
            [
                "name" => "dinding 18mm / tidak droping",
                "code" => "«18",
            ],
            [
                "name" => "Susun 2 kabinet jd 1 ; samping turun kiri",
                "code" => "┌═",
            ],
            [
                "name" => "Panel Mati samping turun kanan kiri",
                "code" => "Pm║",
            ],
        ]);
    }
}
