<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

  public function run(): void
    {
        $this->call([

            StatusSeeder::class,
            UlogaSeeder::class,
            OblastSeeder::class,

            UserSeeder::class,
            NaucniRadSeeder::class,

            AutorstvoSeeder::class,
            RecenzijaSeeder::class,
            StavkaRecenzijeSeeder::class,
            ReferenceSeeder::class,
            FajlRadaSeeder::class,
        ]);
    }
}
