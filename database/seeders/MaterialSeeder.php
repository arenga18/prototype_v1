<?php

namespace Database\Seeders;

use App\Models\Material;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class MaterialSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Material::insert([
            [
                "id" => "101079",
                "cat" => "Ply lantrex Polos",
                "name" => "Ply LANTREX 3 mm polos untuk pintu",
                "qty" => "",
                "unit" => "Lbr",
                "note" => "Anti Rayap",
            ],
            [
                "id" => "210481",
                "cat" => "Edging",
                "name" => "Edg Decor 1723 B  uk : 23x1",
                "qty" => "",
                "unit" => "",
                "note" => "",
            ],
            [
                "id" => "101110",
                "cat" => "UPVC",
                "name" => "POLA BOARD GREY NATURAL 12 MM 4X8",
                "qty" => "",
                "unit" => "Lbr",
                "note" => "Non Anti Rayap",
            ],
            [
                "id" => "103939",
                "cat" => "MDF Polos",
                "name" => "Mdf 11 mm polos untuk pintu",
                "qty" => "",
                "unit" => "Lbr",
                "note" => "Non Anti Rayap",
            ],
        ]);
    }
}
