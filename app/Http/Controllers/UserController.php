<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Uloga;
use App\Models\Status;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Http\Resources\UserResource;
use Carbon\Carbon;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $users = User::with('uloge')->get();
        return UserResource::collection($users);
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
            'uloga_id' => 'required|exists:uloga,UlogaID'
        ]);

        $validated['password'] = Hash::make($validated['password']);

    
        $korisnik = User::create($validated);

        $korisnik->load('uloge');
    
        return response()->json([
            'message' => 'Korisnik uspešno kreiran i uloga dodeljena',
            'user' => new UserResource($korisnik)
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
            'Biografija' => 'nullable|string',
        ]);

        $user->update($validated);

        return response()->json([
            'poruka' => 'Profil uspešno ažuriran!',
            'podaci' => new UserResource($user)
        ], 200);
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

    public function dodeliUlogu(Request $request, string $id) {
        // 1. Validacija - tražimo da 'uloge' bude niz (array)
        $request->validate([
            'uloge' => 'required|array|min:1|max:3',
            'uloge.*' => 'exists:uloga,UlogaID', // Provera da svaki ID u nizu postoji u tabeli uloga
        ]);

        $korisnik = User::findOrFail($id); //Traži korisnika

        // 2. Priprema podataka za pivot tabelu
        $noveUloge = [];
        foreach ($request->uloge as $idUloge) {
            $noveUloge[$idUloge] = ['Datum' => Carbon::now()];
        }

        // 3. Sync briše sve stare i postavlja samo ove nove
        $korisnik->uloge()->sync($noveUloge);

        return response()->json([
            'poruka' => 'Uloge su uspešno ažurirane.',
            'korisnik' => $korisnik->ImePrezime,
            'trenutne_uloge' => $korisnik->uloge()->get(['uloga.UlogaID', 'Naziv'])
        ]);
    }

    public function istrazivaci()
    {
        $istrazivaci = User::whereHas('uloge', function ($q) {
                $q->where('uloga.UlogaID', Uloga::ISTRAZIVAC);
            })
            ->withCount(['naucniRadovi as brojObjavljenihRadova' => function ($q) {
                $q->where('StatusID', Status::OBJAVLJEN);
            }])
            ->orderBy('ImePrezime')
            ->get(['ZapID', 'ImePrezime', 'Biografija']);

        return response()->json([
            'podaci' => $istrazivaci->map(function ($istrazivac) {
                return [
                    'id'                     => $istrazivac->ZapID,
                    'imePrezime'             => $istrazivac->ImePrezime,
                    'biografija'             => $istrazivac->Biografija,
                    'brojObjavljenihRadova'  => $istrazivac->brojObjavljenihRadova,
                ];
            })
        ], 200);
    }

    public function profilIstrazivaca(string $id)
    {
        $istrazivac = User::whereHas('uloge', function ($q) {
                $q->where('uloga.UlogaID', Uloga::ISTRAZIVAC);
            })
            ->with(['naucniRadovi' => function ($q) {
                $q->where('StatusID', Status::OBJAVLJEN)->orderBy('godina', 'desc');
            }])
            ->find($id);

        if (!$istrazivac) {
            return response()->json([
                'message' => 'Istrazivac sa ovim ID-em ne postoji.'
            ], 404);
        }

        return response()->json([
            'id'          => $istrazivac->ZapID,
            'imePrezime'  => $istrazivac->ImePrezime,
            'biografija'  => $istrazivac->Biografija,
            'radovi'      => $istrazivac->naucniRadovi->map(function ($rad) {
                return [
                    'id'            => $rad->NRID,
                    'naslov'        => $rad->naslov,
                    'godina'        => $rad->godina,
                    'doi'           => $rad->DOI,
                    'spoljniAutori' => $rad->spoljniAutori,
                ];
            })->values(),
        ], 200);
    }
}
