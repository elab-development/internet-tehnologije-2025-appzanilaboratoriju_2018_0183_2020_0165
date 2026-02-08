<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;


class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return User::with('uloge')->get();
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'ImePrezime' => 'required|string|max:255',
            'email' => 'required|email|unique:korisnik,email',
            'password' => 'required|min:6',
            'Biografija' => 'nullable|string',
            'uloga_id' => 'required|exists:uloga,UlogaID' // Moraš znati koju ulogu dodeljuješ
        ]);

        $validated['password'] = Hash::make($validated['password']);

        // 1. Kreiraj korisnika
        $korisnik = User::create($validated);

        // 2. Poveži ga sa ulogom u tabeli DodelaUloge
        // Ovo će automatski popuniti ZapID i UlogaID
        // UserController.php oko linije 49
        $korisnik->uloge()->attach($request->uloga_id, [
            'Datum' => now() // Ovo šalje trenutni datum i vreme
        ]);
        return response()->json([
            'message' => 'Korisnik uspešno kreiran i uloga dodeljena',
            'user' => $korisnik
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        return User::with('uloge')->findOrFail($id);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $user = User::findOrFail($id);

        $validated = $request->validate([
            'ImePrezime' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:korisnik,email,' . $user->ZapID . ',ZapID',
            'password' => 'nullable|min:6',
            'Biografija' => 'nullable|string',
        ]);

        if(isset($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        }

        $user->update($validated);

        return $user;
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $user = User::findOrFail($id);
        $user->delete();

        return response()->json(['message' => 'Korinsik obrisan']);
    }
}
