<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\NaucniRad;
use Illuminate\Support\Facades\Validator;
use App\Http\Resources\NaucniRadResource;

class NaucniRadController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $radovi = NaucniRad::with(['oblasti', 'status', 'autori'])->get();
        return NaucniRadResource::collection($radovi);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'naslov'      => 'required|string|max:255',
            'abstrakt'    => 'required|string',
            'kljucneReci' => 'required|string',
            'godina'      => 'required|integer',
            'StatusID'    => 'required|exists:status,StatusID',
            'grupaId'     => 'required|integer',
        ]);

        $naucniRad = NaucniRad::create($validatedData);

        if ($request->has('oblasti')) {
            $naucniRad->oblasti()->attach($request->oblasti);
        }

        if ($request->has('autori')) {
            $naucniRad->autori()->attach($request->autori);
        }

        $naucniRad->load(['oblasti', 'status', 'autori']);

        return response()->json([
            'poruka' => 'Rad uspešno dodat!',
            'podaci' => new NaucniRadResource($naucniRad)
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $rad = NaucniRad::with(['oblasti', 'status', 'autori'])->findOrFail($id);
        return new NaucniRadResource($rad);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $rad = NaucniRad::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'naslov'      => 'sometimes|string|max:255',
            'abstrakt'    => 'sometimes|string',
            'kljucneReci' => 'sometimes|string', // Omogućeno ažuriranje ključnih reči
            'godina'      => 'sometimes|integer',
            'grupaId'     => 'nullable|integer',
            'verzija'     => 'nullable|integer',
            'StatusID'    => 'sometimes|exists:status,StatusID',
            'oblasti'     => 'array',
            'oblasti.*'   => 'exists:oblast,oblastId',
            'autori'      => 'array',
            'autori.*'    => 'exists:korisnik,ZapID',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors'  => $validator->errors()
            ], 422);
        }

        // Ažuriranje podataka u bazi
        $rad->update($validator->validated());

        // Sinhronizacija Many-to-Many relacija (sync briše stare i dodaje nove veze)
        if ($request->has('oblasti')) {
            $rad->oblasti()->sync($request->oblasti);
        }
        if ($request->has('autori')) {
            $rad->autori()->sync($request->autori);
        }

        return response()->json([
            'poruka' => 'Rad uspešno ažuriran!',
            'podaci' => new NaucniRadResource($rad->load(['status', 'oblasti', 'autori']))
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $rad = NaucniRad::findOrFail($id);
        
        // Eloquent će automatski ukloniti veze iz pivot tabela ako je podešen onDelete cascade u migracijama
        $rad->delete();

        return response()->json([
            'poruka' => 'Naučni rad je trajno uklonjen iz baze.'
        ], 200);
    }
}