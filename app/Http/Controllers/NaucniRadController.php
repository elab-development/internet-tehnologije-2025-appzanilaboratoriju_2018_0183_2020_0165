<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\NaucniRad;
use App\Models\User;
use App\Models\Uloga;
use App\Models\Recenzija;
use App\Models\Status;
use Illuminate\Support\Facades\Validator;
use App\Http\Resources\NaucniRadResource;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class NaucniRadController extends Controller
{

    public function index(Request $request)
    {

        $query = NaucniRad::with(['oblasti', 'status', 'autori']);

       if ($request->has('pretraga')) {
        $pojam = $request->query('pretraga');

        $query->where(function($q) use ($pojam) {

            $q->where('kljucneReci', 'LIKE', '%' . $pojam . '%')
            ->orWhere('naslov', 'LIKE', '%' . $pojam . '%')

            ->orWhereHas('oblasti', function($q2) use ($pojam) {
                $q2->where('naziv', 'LIKE', '%' . $pojam . '%');
            })

            ->orWhereHas('autori', function($q3) use ($pojam) {
                $q3->where('ImePrezime', 'LIKE', '%' . $pojam . '%');
            });

        });
        }

        $radovi = $query->get();

        return NaucniRadResource::collection($radovi);
    }

    public function store(Request $request)
    {

        $validatedData = $request->validate([
            'naslov'      => 'required|string|max:255',
            'abstrakt'    => 'required|string',
            'kljucneReci' => 'required|string',
            'godina'      => 'required|integer',
            'grupaId'     => 'required|integer',
            'oblasti'     => 'required|array|min:1',
            'oblasti.*'   => 'exists:oblast,oblastId',
            'autori'      => 'nullable|array|max:2',
            'autori.*'    => 'exists:korisnik,ZapID',
        ]);

        if ($request->has('autori') && !empty($request->autori)) {
            $validniIstrazivaciCount = User::whereIn('ZapID', $request->autori)
                ->whereHas('uloge', function($q) {
                    $q->where('uloga.UlogaID', Uloga::ISTRAZIVAC);
                })->count();

            if ($validniIstrazivaciCount !== count($request->autori)) {
                return response()->json([
                    'error' => 'Svi koautori moraju imati ulogu Istraživač.'
                ], 422);
            }
        }

        $naucniRad = DB::transaction(function () use ($request, $validatedData) {

            $naucniRad = NaucniRad::create(array_merge($validatedData, ['StatusID' => Status::CEKA_RECENZIJU]));

            $naucniRad->oblasti()->attach($request->oblasti);

            $sviAutori = array_unique(array_merge([auth()->id()], $request->autori ?? []));
            $naucniRad->autori()->attach($sviAutori);

            $recenzent = User::whereHas('uloge', function($q) {
                    $q->where('uloga.UlogaID', Uloga::RECENZENT);
                })
                ->whereNotIn('ZapID', $sviAutori)
                ->inRandomOrder()
                ->first();

            if ($recenzent) {
                Recenzija::create([
                    'NRID'  => $naucniRad->NRID,
                    'ZapID' => $recenzent->ZapID,
                    'Datum' => now()
                ]);
            }

            return $naucniRad;
        });

        $naucniRad->load(['oblasti', 'status', 'autori']);

        return response()->json([
            'poruka' => 'Rad uspešno dodat i dodeljen recenzentu.',
            'podaci' => new NaucniRadResource($naucniRad)
        ], 201);
    }

    public function show($id)
    {
        $rad = NaucniRad::with(['oblasti', 'status', 'autori'])
            ->where('StatusID', Status::OBJAVLJEN)
            ->find($id);

        if (!$rad) {
            return response()->json([
                'message' => 'Objavljen rad sa ovim ID-em ne postoji.'
            ], 404);
        }

        return new NaucniRadResource($rad);
    }

    public function update(Request $request, string $id)
    {
        $rad = NaucniRad::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'naslov'      => 'sometimes|string|max:255',
            'abstrakt'    => 'sometimes|string',
            'kljucneReci' => 'sometimes|string',
            'godina'      => 'sometimes|integer',
            'grupaId'     => 'nullable|integer',
            'verzija'     => 'nullable|integer',
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

        $rad->update($validator->validated());

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

    public function destroy(string $id)
    {
        $rad = NaucniRad::findOrFail($id);

        $rad->delete();

        return response()->json([
            'poruka' => 'Naučni rad je trajno uklonjen iz baze.'
        ], 200);
    }

    public function mojiRadovi()
    {

        $korisnik = Auth::user();

        $radovi = $korisnik->naucniRadovi()
                        ->with(['oblasti', 'status'])
                        ->orderBy('godina', 'desc')
                        ->get();

        return NaucniRadResource::collection($radovi);
    }

    public function prikaziRecenziju($id)
    {

        $rad = NaucniRad::with(['status', 'recenzije.stavke.status', 'recenzije.korisnik'])->find($id);

        if (!$rad) {
            return response()->json(['message' => 'Rad nije pronađen.'], 404);
        }

        if (!$rad->autori->contains('ZapID', Auth::id())) {
            return response()->json(['message' => 'Niste autor ovog rada.'], 403);
        }

        if ($rad->recenzije->isEmpty()) {
            return response()->json([
                'id' => $rad->NRID,
                'naslov' => $rad->naslov,
                'status' => $rad->status->Naziv,
                'recenzije' => [],
                'message' => 'Još uvek nema urađenih recenzija za ovaj rad.'
            ], 200);
        }

        return response()->json([
            'id' => $rad->NRID,
            'naslov' => $rad->naslov,
            'status' => $rad->status->Naziv,
            'recenzije' => $rad->recenzije->map(function ($recenzija) {
                return [
                    'id'        => $recenzija->RecenzijaID,
                    'datum'     => $recenzija->Datum,
                    'recenzent' => $recenzija->korisnik->ImePrezime ?? 'Anonimni recenzent',
                    'stavke'    => $recenzija->stavke->map(function ($stavka) {
                        return [
                            'id'       => $stavka->StavkaID,
                            'komentar' => $stavka->Komentar,
                            'status'   => $stavka->status->Naziv ?? null,
                            'datum'    => $stavka->created_at,
                        ];
                    })->values(),
                ];
            })->values()
        ], 200);
    }

    public function objavljeniRadovi(Request $request)
    {
        $query = NaucniRad::with(['autori', 'oblasti'])
            ->where('StatusID', Status::OBJAVLJEN);

        if ($request->has('oblast_id')) {
            $query->whereHas('oblasti', function($q) use ($request) {

                $q->where('Oblast.OblastID', $request->oblast_id);
            });
        }

        if ($request->has('autor_id')) {
            $query->whereHas('autori', function($q) use ($request) {
                $q->where('Autorstvo.ZapID', $request->autor_id);
            });
        }

        if ($request->has('keyword')) {
        $s = $request->query('keyword');

        $query->where('kljucneReci', 'LIKE', '%' . $s . '%');
        }

        $query->orderBy('godina', 'desc');

        $radovi = $query->get();

        if ($radovi->isEmpty()) {
        return response()->json([
            'success' => true,
            'message' => 'Nažalost, trenutno nema objavljenih radova sa takvim parametrima.',
            'data' => []
        ], 200);
        }
        return NaucniRadResource::collection($radovi);
    }

    public function citati(string $id)
    {
        $rad = NaucniRad::where('StatusID', Status::OBJAVLJEN)->find($id);

        if (!$rad) {
            return response()->json([
                'message' => 'Objavljen rad sa ovim ID-em ne postoji.'
            ], 404);
        }

        $samoObjavljeni = function ($q) {
            $q->where('StatusID', Status::OBJAVLJEN)->orderBy('godina', 'desc');
        };

        $rad->load([
            'citira'          => $samoObjavljeni,
            'citiranOdStrane' => $samoObjavljeni,
        ]);

        $mapiraj = function ($radovi) {
            return $radovi->map(function ($r) {
                return [
                    'id'     => $r->NRID,
                    'naslov' => $r->naslov,
                    'godina' => $r->godina,
                ];
            })->values();
        };

        return response()->json([
            'id'              => $rad->NRID,
            'naslov'          => $rad->naslov,
            'brojCitata'      => $rad->citiranOdStrane->count(),
            'citiranOdStrane' => $mapiraj($rad->citiranOdStrane),
            'brojReferenci'   => $rad->citira->count(),
            'citira'          => $mapiraj($rad->citira),
        ], 200);
    }

    public function spoljniCitati(string $id)
    {
        $rad = NaucniRad::where('StatusID', Status::OBJAVLJEN)->find($id);

        if (!$rad) {
            return response()->json([
                'message' => 'Objavljen rad sa ovim ID-em ne postoji.'
            ], 404);
        }

        if (empty($rad->DOI)) {
            return response()->json([
                'message' => 'Rad nema DOI pa se citiranost ne moze proveriti kod spoljnih servisa.'
            ], 422);
        }

        $crossRef = Cache::remember('spoljni-citati:' . $rad->DOI, now()->addHours(6), function () use ($rad) {
            return $this->citatiSaCrossRefa($rad->DOI);
        });

        return response()->json([
            'id'       => $rad->NRID,
            'naslov'   => $rad->naslov,
            'doi'      => $rad->DOI,
            'crossRef' => $crossRef,
        ], 200);
    }

    private function citatiSaCrossRefa(string $doi): array
    {
        try {
            $odgovor = Http::timeout(10)
                ->withHeaders(['User-Agent' => 'NILApp/1.0 (https://github.com/elab-development)'])
                ->get('https://api.crossref.org/works/' . rawurlencode($doi));
        } catch (\Throwable $e) {
            return ['dostupno' => false, 'razlog' => 'Servis nije dostupan.'];
        }

        if ($odgovor->status() === 404) {
            return ['dostupno' => true, 'doiPotvrdjen' => false, 'razlog' => 'CrossRef ne poznaje ovaj DOI.'];
        }

        if ($odgovor->failed()) {
            return ['dostupno' => false, 'razlog' => 'CrossRef je vratio status ' . $odgovor->status() . '.'];
        }

        $poruka = $odgovor->json('message') ?? [];

        return [
            'dostupno'     => true,
            'doiPotvrdjen' => true,
            'brojCitata'   => $poruka['is-referenced-by-count'] ?? null,
            'casopis'      => $poruka['container-title'][0] ?? null,
            'izdavac'      => $poruka['publisher'] ?? null,
            'tip'          => $poruka['type'] ?? null,
        ];
    }

    public function srodniRadovi(string $id)
    {
        $rad = NaucniRad::find($id);

        if (!$rad) {
            return response()->json([
                'message' => 'Rad sa ovim ID-em ne postoji.'
            ], 404);
        }

        $pojam = trim((string) $rad->kljucneReci);

        if ($pojam === '') {
            return response()->json([
                'message' => 'Rad nema kljucne reci pa se srodni radovi ne mogu potraziti.'
            ], 422);
        }

        $srodni = Cache::remember('srodni-radovi:' . md5($pojam) . ':' . $rad->NRID, now()->addHours(6), function () use ($pojam, $rad) {
            return $this->pretragaNaOpenAlexu($pojam, $rad->DOI);
        });

        return response()->json([
            'id'          => $rad->NRID,
            'naslov'      => $rad->naslov,
            'kljucneReci' => $rad->kljucneReci,
            'srodni'      => $srodni,
        ], 200);
    }

    private function pretragaNaOpenAlexu(string $pojam, ?string $doiZaIzuzeti): array
    {
        try {
            $odgovor = Http::timeout(15)
                ->withHeaders(['User-Agent' => 'NILApp/1.0 (mailto:nilapp@example.com)'])
                ->get('https://api.openalex.org/works', [
                    'search'   => $pojam,
                    'select'   => 'doi,title,publication_year,cited_by_count,authorships',
                    'per-page' => 6,
                ]);
        } catch (\Throwable $e) {
            return ['dostupno' => false, 'razlog' => 'Servis nije dostupan.'];
        }

        if ($odgovor->failed()) {
            return ['dostupno' => false, 'razlog' => 'OpenAlex je vratio status ' . $odgovor->status() . '.'];
        }

        $rezultati = [];

        foreach (($odgovor->json('results') ?? []) as $stavka) {
            $doi = $stavka['doi'] ?? null;
            $goliDoi = $doi ? strtolower(str_replace('https://doi.org/', '', $doi)) : null;

            if ($doiZaIzuzeti && $goliDoi === strtolower($doiZaIzuzeti)) {
                continue;
            }

            $autori = [];
            foreach (array_slice($stavka['authorships'] ?? [], 0, 3) as $autorstvo) {
                $ime = $autorstvo['author']['display_name'] ?? null;
                if ($ime) {
                    $autori[] = $ime;
                }
            }

            $rezultati[] = [
                'naslov'     => $stavka['title'] ?? null,
                'godina'     => $stavka['publication_year'] ?? null,
                'doi'        => $goliDoi,
                'brojCitata' => $stavka['cited_by_count'] ?? null,
                'autori'     => implode(', ', $autori),
            ];

            if (count($rezultati) >= 5) {
                break;
            }
        }

        return [
            'dostupno' => true,
            'broj'     => count($rezultati),
            'radovi'   => $rezultati,
        ];
    }
}
