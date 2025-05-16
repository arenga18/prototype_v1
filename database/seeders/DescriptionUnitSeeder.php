<?php

namespace Database\Seeders;

use App\Models\DescriptionUnit;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DescriptionUnitSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DescriptionUnit::insert([
            [
                "name" => "Back Panel",
                "code" => "B'Panel",
            ],
            [
                "name" => "End Panel",
                "code" => "E'panel",
            ],
            [
                "name" => "HC Sudut L",
                "code" => "HC∟◄",
            ],
        ]);
    }
}
