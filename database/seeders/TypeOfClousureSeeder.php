<?php

namespace Database\Seeders;

use App\Models\TypeOfClosure;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TypeOfClousureSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        TypeOfClosure::insert([
            [
                "name" => "HPL Edg+HPL edg handel kaca",
                "code" => "H+h═",
            ],
            [
                "name" => "Veneer Edg+kaca S4",
                "code" => "Vk4",
            ],
            [
                "name" => "Cermin S4",
                "code" => "C4",
            ],
        ]);
    }
}
