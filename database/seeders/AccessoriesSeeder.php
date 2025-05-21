<?php

namespace Database\Seeders;

use App\Models\Accessories;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AccessoriesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Accessories::insert([
            [
                "name" => "Rak Botol",
                "code" => "br",
            ],
            [
                "name" => "Rak Sendok+rak piring",
                "code" => "srpr",
            ],
            [
                "name" => "soket bohlam",
                "code" => "☼",
            ],
        ]);
    }
}
