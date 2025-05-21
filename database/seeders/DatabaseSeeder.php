<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            DescriptionUnitSeeder::class,
            BoxCarcaseShapeSeeder::class,
            FinishingSeeder::class,
            LayerPositionSeeder::class,
            ClosingSystemSeeder::class,
            NumberOfClosureSeeder::class,
            TypeOfClousureSeeder::class,
            HandleSeeder::class,
            AccessoriesSeeder::class,
            LampSeeder::class,
            PlinthSeeder::class
        ]);
    }
}
