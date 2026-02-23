<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    // REGISTRACIJA KORISNIKA
    public function register(Request $request)
    {
        $fields = $request->validate([
            'ImePrezime' => 'required|string|max:255',
            'email' => 'required|string|unique:korisnik,email',
            'password' => 'required|string|min:6|confirmed',
            'Biografija' => 'nullable|string',
            'uloga_id' => 'required|exists:uloga,UlogaID' // Obavezno pri kreiranju
        ]);

        $korisnik = User::create([
            'ImePrezime' => $fields['ImePrezime'],
            'email' => $fields['email'],
            'password' => Hash::make($fields['password']),
            'Biografija' => $fields['Biografija'] ?? null,
        ]);

        // Povezujemo korisnika sa ulogom u pivot tabeli
        $korisnik->uloge()->attach($fields['uloga_id']);

        return response([
            'message' => 'Admin je uspešno kreirao novog korisnika.',
            'user' => $korisnik
        ], 201);
    }

    // LOGIN KORISNIKA
    public function login(Request $request)
    {
        $fields = $request->validate([
            'email' => 'required|string',
            'password' => 'required|string',
            'uloga_id' => 'required|exists:uloga,UlogaID'
        ]);

        $korisnik = User::where('email', $fields['email'])->first();

        if (!$korisnik || !Hash::check($fields['password'], $korisnik->password)) {
            return response([
                'message' => 'Pogrešan email ili lozinka.'
            ], 401);
        }

        $imaUlogu = $korisnik->uloge()->where('Uloga.UlogaID', $fields['uloga_id'])->exists();

        if (!$imaUlogu) {
            return response([
                'message' => 'Nemate dozvolu da se ulogujete sa ovom ulogom.'
            ], 403);
        }

        $token = $korisnik->createToken('myapptoken')->plainTextToken;

        return response([
            'korisnik' => $korisnik,
            'token' => $token,
            'trenutna_uloga' => $fields['uloga_id']
        ], 200);
    }

    // LOGOUT KORISNIKA
    public function logout(Request $request)
    {
        // Brišemo trenutni token kojim je korisnik pristupio
        $request->user()->currentAccessToken()->delete();

        return response([
            'message' => 'Uspešno ste se izlogovali.'
        ], 200);
    }
}