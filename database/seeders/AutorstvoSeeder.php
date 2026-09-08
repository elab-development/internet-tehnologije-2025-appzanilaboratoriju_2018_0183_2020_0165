<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\NaucniRad;
use App\Models\Uloga;

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

        // Autori mogu biti samo Istrazivaci, isto pravilo koje NaucniRadController::store namece
        $istrazivaci = User::whereHas('uloge', function ($q) {
            $q->where('uloga.UlogaID', Uloga::ISTRAZIVAC);
        })->pluck('ZapID');

        if ($istrazivaci->isEmpty()) {
            return;
        }

       // Uzmi samo interno predate radove
        $radovi = NaucniRad::whereNull('DOI')->get();

        foreach ($radovi as $rad) {
            // Podnosilac plus najvise dva koautora, kao u store()
            $koliko = min(rand(1, 3), $istrazivaci->count());
            $autori = $istrazivaci->shuffle()->take($koliko);

            // Sync umesto syncWithoutDetaching da se autori ne gomilaju pri ponovnom pokretanju
            $rad->autori()->sync($autori);
        }
    }
}
