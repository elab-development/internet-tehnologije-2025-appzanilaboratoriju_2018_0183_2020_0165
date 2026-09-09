<?php

namespace App\Http\Controllers;

use App\Models\StavkaRecenzije;
use Illuminate\Http\Request;

class StavkaRecenzijeController extends Controller
{

    public function index()
    {
        return StavkaRecenzije::all();
    }

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
            'message' => 'Stavka je uspešno sačuvana',
            'data' => $novaStavka
        ], 201);
    }

    public function show(string $id)
    {
        return StavkaRecenzije::findOrFail($id);
    }

    public function update(Request $request, string $id)
    {

    }

    public function destroy(string $id)
    {

    }
}
