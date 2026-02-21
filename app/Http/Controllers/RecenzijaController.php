<?php

namespace App\Http\Controllers;

use App\Models\Recenzija;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\NaucniRadResource;

class RecenzijaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Recenzija::all();
    }

    /**
     * Store a newly created resource in storage.
     */
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

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        return Recenzija::findOrFail($id);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //ne treba nam
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $recenzija = Recenzija::findOrFail($id);
        $recenzija->delete();

        return response()->json(['message' => 'Dodela je uspešno obrisana']);
    }

    public function dodeljeniRadovi()
    {
         $recenzentId = Auth::id();

        // 1. Pronalazimo sve recenzije dodeljene ovom recenzentu
        $zaduzenja = Recenzija::where('ZapID', $recenzentId)
            ->with(['naucniRad.status', 'naucniRad.oblasti', 'naucniRad.autori'])
            ->get();

        // 2. Izvlačimo samo objekte Naučni rad iz tih recenzija
        $radovi = $zaduzenja->pluck('naucniRad');

        // 3. Vraćamo ih kroz NaucniRadResource da bi JSON bio čist
        return NaucniRadResource::collection($radovi);
    }
}
