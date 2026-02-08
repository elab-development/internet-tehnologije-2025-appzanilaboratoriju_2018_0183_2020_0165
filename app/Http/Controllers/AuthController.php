<?php

namespace App\Http\Controllers;

use App\Models\Korisnik;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function login(Request $request)
    {
       //validacija unetih podataka
        $fields = $request->validate([
            'email' => 'required|string',
            'password' => 'required|string',
            'uloga_id' => 'required|exists:uloga,id'
        ]);


        $korisnik = Korisnik::where('email', $fields['email'])->first();


        if (!$korisnik || !Hash::check($fields['password'], $korisnik->password)) {
            return response([
                'message' => 'Pogrešan email ili lozinka.'
            ], 401);
        }

        // provera da li ima tu ulogu
        $imaUlogu = $korisnik->uloge()->where('uloga.id', $fields['uloga_id'])->exists();

        if (!$imaUlogu) {
            return response([
                'message' => 'Nemate mogucnost da se ulogujete u datu ulogu'
            ], 403);
        }


        $token = $korisnik->createToken('myapptoken')->plainTextToken;

        return response([
            'user' => $korisnik,
            'token' => $token,
            'current_role' => $fields['uloga_id']
        ], 200);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return ['message' => 'Izlogovan'];
    }

}
