<?php

namespace App\Http\Controllers;

use App\Models\Recenzija; // spajamo sa modelom
use Illuminate\Http\Request;

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
}
