<?php

namespace App\Http\Controllers;

use App\Models\Recenzija;
use App\Models\StavkaRecenzije;
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

    public function update(Request $request, string $id)
    {

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
            ->with(['naucniRad.status', 'naucniRad.oblasti', 'naucniRad.autori'])
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
