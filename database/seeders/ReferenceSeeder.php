<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\NaucniRad;

class ReferenceSeeder extends Seeder
{

    public function run(): void
    {
        $radovi = NaucniRad::whereNotNull('DOI')
            ->whereNotNull('godina')
            ->orderBy('godina')
            ->get();

        foreach ($radovi as $rad) {
            $stariji = $radovi->where('godina', '<', $rad->godina);

            if ($stariji->isEmpty()) {
                continue;
            }

            $koliko = min(3, $stariji->count());
            $citirani = $stariji->shuffle()->take($koliko)->pluck('NRID');

            $rad->citira()->syncWithoutDetaching($citirani);
        }
    }
}
