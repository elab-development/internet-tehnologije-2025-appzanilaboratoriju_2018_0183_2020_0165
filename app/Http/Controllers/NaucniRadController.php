<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\NaucniRad;
use Illuminate\Support\Facades\Validator;

class NaucniRadController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return NaucniRad::with(['oblasti', 'status', 'autori'])->get();

    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
      // Validacija
        $validator = Validator::make($request->all(), [
            'naslov'   => 'required|string|max:255',
            'abstrakt' => 'required|string',
            'godina'   => 'required|integer',
            'grupaId'  => 'nullable|integer',
            'verzija'  => 'nullable|integer',
            'StatusID' => 'required|exists:status,StatusID', // FK
            'oblasti'  => 'array', // niz ID-jeva oblasti
            'oblasti.*'=> 'exists:oblast,oblastId',
            'autori'   => 'array', // niz ID-jeva korisnika
            'autori.*' => 'exists:korisnik,ZapID',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors'  => $validator->errors()
            ], 422);
        }

        // Kreiranje rada
        $rad = NaucniRad::create($validator->validated());

        // Pivot tabele
        if ($request->has('oblasti')) {
            $rad->oblasti()->sync($request->oblasti);
        }
        if ($request->has('autori')) {
            $rad->autori()->sync($request->autori);
        }

        return response()->json($rad->load(['status', 'oblasti', 'autori']), 201);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        return NaucniRad::with(['status', 'oblasti', 'autori'])->findOrFail($id);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $rad = NaucniRad::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'naslov'   => 'sometimes|string|max:255',
            'abstrakt' => 'sometimes|string',
            'godina'   => 'sometimes|integer',
            'grupaId'  => 'nullable|integer',
            'verzija'  => 'nullable|integer',
            'StatusID' => 'sometimes|exists:status,StatusID',
            'oblasti'  => 'array',
            'oblasti.*'=> 'exists:oblast,oblastId',
            'autori'   => 'array',
            'autori.*' => 'exists:korisnik,ZapID',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors'  => $validator->errors()
            ], 422);
        }

        $rad->update($validator->validated());

        // Pivot tabele
        if ($request->has('oblasti')) {
            $rad->oblasti()->sync($request->oblasti);
        }
        if ($request->has('autori')) {
            $rad->autori()->sync($request->autori);
        }

        return response()->json($rad->load(['status', 'oblasti', 'autori']));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $rad = NaucniRad::findOrFail($id);
        $rad->delete();

        return response()->json(['message' => 'Rad obrisan']);
    }
}
