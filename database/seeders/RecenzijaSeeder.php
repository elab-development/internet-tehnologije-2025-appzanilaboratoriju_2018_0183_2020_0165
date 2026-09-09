<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Uloga;
use App\Models\NaucniRad;
use App\Models\Recenzija;

class RecenzijaSeeder extends Seeder
{

    public function run(): void
    {

        $recenzenti = User::whereHas('uloge', function ($q) {
            $q->where('uloga.UlogaID', Uloga::RECENZENT);
        })->orderBy('ZapID')->pluck('ZapID');

        if ($recenzenti->isEmpty()) {
            return;
        }

        $this->obrisiNeispravne($recenzenti);

        $radovi = NaucniRad::whereNull('DOI')->with('autori')->orderBy('NRID')->get();
        $redni = 0;

        foreach ($radovi as $rad) {
            if ($rad->recenzije()->exists()) {
                continue;
            }

            $moguci = $recenzenti->diff($rad->autori->pluck('ZapID'))->values();

            if ($moguci->isEmpty()) {
                continue;
            }

            Recenzija::create([
                'NRID'  => $rad->NRID,
                'ZapID' => $moguci[$redni % $moguci->count()],
                'Datum' => now(),
            ]);

            $redni++;
        }
    }

    private function obrisiNeispravne($recenzenti): void
    {

        Recenzija::whereHas('naucniRad', function ($q) {
                $q->whereNotNull('DOI');
            })
            ->orWhereNotIn('ZapID', $recenzenti)
            ->orWhereIn('NRID', function ($q) {
                $q->select('NRID')->from('Autorstvo')
                  ->whereColumn('Autorstvo.ZapID', 'recenzija.ZapID');
            })
            ->delete();
    }
}
