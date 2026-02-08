<?php

namespace App\Http\Controllers;

use App\Models\StavkaRecenzije; //spajanje sa modelom
use Illuminate\Http\Request;

class StavkaRecenzijeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return StavkaRecenzije::all();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'komentar' => 'required|string',
            'datum_stavke' => 'required|date',
            'recenzija_id' => 'required|exists:recenzija,id',
            'status_id' => 'required|exists:status,id',
        ]);

        $novaStavka = StavkaRecenzije::create($validatedData);

        return response()->json([
            'message' => 'Ocena i komentar su uspešno sačuvani.',
            'data' => $novaStavka
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        return StavkaRecenzije::findOrFail($id);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //ne pravimo, jer cuvamo istorijurecenzija
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //ne pravimo, jer cuvamo istorijurecenzija
    }
}
