<?php

namespace App\Http\Controllers;

use App\Models\Recenzija;
use App\Models\NaucniRad;
use App\Models\User;
use App\Models\Uloga;
use App\Models\StavkaRecenzije;
use App\Models\Status;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\NaucniRadResource;
use App\Http\Resources\RecenzijaResource;

class RecenzijaController extends Controller
{

    public function index()
    {
        return Recenzija::all();
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'NRID' => 'required|exists:NaucniRad,NRID',
            'ZapID' => 'required|exists:korisnik,ZapID',
            'Datum' => 'sometimes|date',
        ]);

        $novaRecenzija = Recenzija::create($validatedData);

        return response()->json($novaRecenzija, 201);
    }

    public function show(string $id)
    {
        return Recenzija::findOrFail($id);
    }

    public function dodeliRecenzenta(Request $request, string $id)
    {
        $rad = NaucniRad::with('autori', 'recenzije')->findOrFail($id);

        $validatedData = $request->validate([
            'recenzentId' => 'required|exists:korisnik,ZapID',
        ]);

        if ($rad->StatusID === Status::OBJAVLJEN) {
            return response()->json([
                'message' => 'Objavljenom radu se ne dodeljuje recenzent.'
            ], 422);
        }

        $recenzent = User::whereKey($validatedData['recenzentId'])
            ->whereHas('uloge', function ($q) {
                $q->where('uloga.UlogaID', Uloga::RECENZENT);
            })
            ->first();

        if (!$recenzent) {
            return response()->json([
                'message' => 'Izabrani korisnik nema ulogu Recenzent.'
            ], 422);
        }

        if ($rad->autori->contains('ZapID', $recenzent->ZapID)) {
            return response()->json([
                'message' => 'Autor rada ne može biti njegov recenzent.'
            ], 422);
        }

        if ($rad->recenzije->contains('ZapID', $recenzent->ZapID)) {
            return response()->json([
                'message' => 'Taj recenzent je već dodeljen ovom radu.'
            ], 422);
        }

        $recenzija = Recenzija::create([
            'NRID'  => $rad->NRID,
            'ZapID' => $recenzent->ZapID,
            'Datum' => now(),
        ]);

        return response()->json([
            'poruka'    => 'Recenzent je dodeljen radu.',
            'recenzija' => [
                'id'         => $recenzija->RecenzijaID,
                'imePrezime' => $recenzent->ImePrezime,
            ],
        ], 201);
    }

    public function destroy(string $id)
    {
        $recenzija = Recenzija::findOrFail($id);
        $recenzija->delete();

        return response()->json(['message' => 'Dodela je uspešno obrisana']);
    }

    public function dodeljeniRadovi()
    {
        $recenzije = Recenzija::where('ZapID', Auth::id())
            ->with(['naucniRad.status', 'naucniRad.oblasti', 'naucniRad.autori', 'stavke.status'])
            ->orderByDesc('Datum')
            ->get();

        return RecenzijaResource::collection($recenzije);
    }

    public function sacuvajStavkuRecenzije(Request $request, string $id)
    {

        $validatedData = $request->validate([
            'Komentar'    => 'required|string|min:10',
            'StatusID'    => 'required|exists:status,StatusID',
        ]);

        $recenzija = Recenzija::where('RecenzijaID', $id)
            ->where('ZapID', Auth::id())
            ->firstOrFail();

        if ($recenzija->naucniRad->StatusID !== Status::CEKA_RECENZIJU) {
            return response()->json([
                'message' => 'Ocenjuju se samo radovi koji čekaju recenziju. Ovaj rad je trenutno u statusu: '
                    . $recenzija->naucniRad->status->Naziv . '.'
            ], 422);
        }

        $stavka = StavkaRecenzije::create([
            'RecenzijaID' => $recenzija->RecenzijaID,
            'Komentar'    => $validatedData['Komentar'],
            'StatusID'    => $validatedData['StatusID'],
        ]);

        $recenzija->naucniRad()->update([
            'StatusID' => $validatedData['StatusID']
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Recenzija je uspešno sačuvana i status rada je ažuriran.',
            'data'    => $stavka
        ], 201);
    }

}
