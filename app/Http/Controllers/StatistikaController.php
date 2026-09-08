<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\NaucniRad;
use App\Models\Status;
use Illuminate\Support\Facades\DB;

class StatistikaController extends Controller
{
    public function radoviPoStatusu()
    {
        $brojevi = NaucniRad::select('StatusID', DB::raw('COUNT(*) as broj'))
            ->groupBy('StatusID')
            ->pluck('broj', 'StatusID');

        $podaci = Status::orderBy('StatusID')
            ->get()
            ->map(function ($status) use ($brojevi) {
                return [
                    'oznaka'   => $status->Naziv,
                    'vrednost' => (int) ($brojevi[$status->StatusID] ?? 0),
                ];
            });

        return response()->json([
            'naslov' => 'Radovi po statusu',
            'ukupno' => (int) $podaci->sum('vrednost'),
            'podaci' => $podaci->values(),
        ], 200);
    }

    public function radoviPoGodini()
    {
        $podaci = NaucniRad::select('godina', DB::raw('COUNT(*) as broj'))
            ->whereNotNull('godina')
            ->groupBy('godina')
            ->orderBy('godina')
            ->get()
            ->map(function ($red) {
                return [
                    'oznaka'   => (string) $red->godina,
                    'vrednost' => (int) $red->broj,
                ];
            });

        return response()->json([
            'naslov' => 'Radovi po godini',
            'ukupno' => (int) $podaci->sum('vrednost'),
            'podaci' => $podaci->values(),
        ], 200);
    }
}
