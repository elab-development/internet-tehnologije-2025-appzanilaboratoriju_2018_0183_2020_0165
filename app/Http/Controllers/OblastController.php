<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Oblast;

class OblastController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return response()->json(Oblast::all(), 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'naziv' => 'required|string|unique:oblast,naziv'
        ]);

        $novaOblast = Oblast::create($request->all());

        return response()->json($novaOblast, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $oblast = Oblast::findOrFail($id);
        return response()->json($oblast, 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $oblast = Oblast::findOrFail($id);
        $oblast->delete();

        return response()->json(['message' => 'Oblast uspešno obrisana'], 200);
    }
}
