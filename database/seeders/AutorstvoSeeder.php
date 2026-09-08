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
        // Uvezeni radovi imaju prave autore u koloni spoljniAutori, pa se sa njih
        // skidaju korisnicki nalozi ako ih je raniji seeder zakacio
        foreach (NaucniRad::whereNotNull('DOI')->get() as $uvezen) {
            $uvezen->autori()->detach();
        }

       // Uzmi samo interno predate radove
        $radovi = NaucniRad::whereNull('DOI')->get();

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
