<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Validation\Rule;
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

    public function index()
    {
        $users = User::with('uloge')->get();
        return UserResource::collection($users);
    }

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

    public function show(string $id)
    {
        return new UserResource(User::with('uloge')->findOrFail($id));
    }

    public function update(Request $request, string $id)
    {
        $user = User::findOrFail($id);

        $validated = $request->validate([
            'ImePrezime' => 'sometimes|string|max:255',
            'email'      => ['sometimes', 'string', 'email', 'max:255',
                             Rule::unique('korisnik', 'email')->ignore($user->ZapID, 'ZapID')],
            'password'   => 'sometimes|string|min:6|confirmed',
            'Biografija' => 'nullable|string',
        ]);

        $user->update($validated);

        return response()->json([
            'poruka' => 'Profil uspešno ažuriran!',
            'podaci' => new UserResource($user)
        ], 200);
    }

    public function destroy(Request $request, string $id)
    {
        $user = User::findOrFail($id);

        if ((int) $user->ZapID === (int) $request->user()->ZapID) {
            return response()->json([
                'message' => 'Ne možete obrisati sopstveni nalog.'
            ], 422);
        }

        if ($user->recenzije()->exists() || $user->naucniRadovi()->exists()) {
            return response()->json([
                'message' => 'Korisnik je vezan za radove ili recenzije, pa bi njegovo brisanje uklonilo i tu istoriju.'
            ], 422);
        }

        $user->delete();

        return response()->json(['message' => 'Korisnik je obrisan.']);
    }

    public function dodeliUlogu(Request $request, string $id) {

        $request->validate([
            'uloge' => 'required|array|min:1|max:3',
            'uloge.*' => 'exists:uloga,UlogaID',
        ]);

        $korisnik = User::findOrFail($id);

        $noveUloge = [];
        foreach ($request->uloge as $idUloge) {
            $noveUloge[$idUloge] = ['Datum' => Carbon::now()];
        }

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
