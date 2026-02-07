<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\NaucniRad;

class AutorstvoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
       // Uzmi sve naučne radove
        $radovi = NaucniRad::all();

        foreach ($radovi as $rad) {
            // Odaberi nasumične korisnike kao autore
            $autori = User::inRandomOrder()
                ->take(rand(1, 3)) // 1 do 3 autora po radu
                ->pluck('ZapID');

            // Poveži autore sa radom u pivot tabeli
            $rad->autori()->syncWithoutDetaching($autori);
        }
    }
}
