<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\NaucniRad;
use App\Models\Uloga;

class AutorstvoSeeder extends Seeder
{

    public function run(): void
    {

        foreach (NaucniRad::whereNotNull('DOI')->get() as $uvezen) {
            $uvezen->autori()->detach();
        }

        $istrazivaci = User::whereHas('uloge', function ($q) {
            $q->where('uloga.UlogaID', Uloga::ISTRAZIVAC);
        })->pluck('ZapID');

        if ($istrazivaci->isEmpty()) {
            return;
        }

        $radovi = NaucniRad::whereNull('DOI')->get();

        foreach ($radovi as $rad) {

            $koliko = min(rand(1, 3), $istrazivaci->count());
            $autori = $istrazivaci->shuffle()->take($koliko);

            $rad->autori()->sync($autori);
        }
    }
}
