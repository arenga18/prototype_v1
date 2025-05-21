<?php

namespace Database\Seeders;

use App\Models\NumberOfClosure;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class NumberOfClosureSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        NumberOfClosure::insert([
            [
                "name" => "1 (dalam 2 kotak laci)",
                "code" => "1(2)",
            ],
            [
                "name" => "2 Eq (tiebox/baju lipat)",
                "code" => "2Qtb",
            ],
            [
                "name" => "4 Eq",
                "code" => "4Q",
            ],
        ]);
    }
}
