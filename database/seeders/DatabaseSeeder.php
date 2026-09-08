<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
  public function run(): void
    {
        $this->call([
            // 1️⃣ Šifarnici
            StatusSeeder::class,
            UlogaSeeder::class,
            OblastSeeder::class,

            // 2️⃣ Glavni entiteti
            UserSeeder::class,
            NaucniRadSeeder::class,

            // 3️⃣ Agregacije i slabi objekti
            // Autorstvo ide pre recenzija, jer se recenzent bira medju onima koji nisu autori rada
            AutorstvoSeeder::class,
            RecenzijaSeeder::class,
            StavkaRecenzijeSeeder::class,
            ReferenceSeeder::class,
        ]);
    }
}
