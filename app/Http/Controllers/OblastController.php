<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Oblast;

class OblastController extends Controller
{

    public function index()
    {
        return response()->json(Oblast::all(), 200);
    }

    public function store(Request $request)
    {
        $request->validate([
            'naziv' => 'required|string|unique:oblast,naziv'
        ]);

        $novaOblast = Oblast::create($request->all());

        return response()->json($novaOblast, 201);
    }

    public function show(string $id)
    {
        $oblast = Oblast::findOrFail($id);
        return response()->json($oblast, 200);
    }

    public function update(Request $request, string $id)
    {

    }

    public function destroy(string $id)
    {
        $oblast = Oblast::findOrFail($id);
        $oblast->delete();

        return response()->json(['message' => 'Oblast uspešno obrisana'], 200);
    }
}
