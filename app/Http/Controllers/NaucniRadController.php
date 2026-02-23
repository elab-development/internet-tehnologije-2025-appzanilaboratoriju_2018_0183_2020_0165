<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\NaucniRad;
use App\Models\User;
use App\Models\Uloga;
use App\Models\Recenzija;
use Illuminate\Support\Facades\Validator;
use App\Http\Resources\NaucniRadResource;
use Illuminate\Support\Facades\Auth;

class NaucniRadController extends Controller
{
    /*
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Kreiramo osnovni upit sa svim potrebnim relacijama
        $query = NaucniRad::with(['oblasti', 'status', 'autori']);

        // Proveravamo da li je korisnik poslao parametar 'pretraga' u URL-u
       if ($request->has('pretraga')) {
        $pojam = $request->query('pretraga');

        $query->where(function($q) use ($pojam) {
            // 1. Pretraga po kolonama 'naslov' i 'kljucneReci' u samoj tabeli 'naucniRadovi'
            $q->where('kljucneReci', 'LIKE', '%' . $pojam . '%')
            ->orWhere('naslov', 'LIKE', '%' . $pojam . '%')
            
            // 2. Pretraga kroz relaciju 'oblasti', ovo je nastavak iste funkcije where
            ->orWhereHas('oblasti', function($q2) use ($pojam) { // koristimo whereHas ako tražimo preko veze
                $q2->where('naziv', 'LIKE', '%' . $pojam . '%');
            })
            
            // 3. Pretraga kroz relaciju 'autori'
            ->orWhereHas('autori', function($q3) use ($pojam) {
                $q3->where('ImePrezime', 'LIKE', '%' . $pojam . '%');
            });

            /*Predlozi:
                1. Vremenski opseg kao način pretrage (preko kolone godina)
            */
        });
        }

        $radovi = $query->get();
        
        return NaucniRadResource::collection($radovi);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Validacija
        $validatedData = $request->validate([
            'naslov'      => 'required|string|max:255',
            'abstrakt'    => 'required|string',
            'kljucneReci' => 'required|string',
            'godina'      => 'required|integer',
            'StatusID'    => 'required|exists:status,StatusID',
            'grupaId'     => 'required|integer',
            'oblasti'     => 'required|array|min:1',
            'autori'      => 'nullable|array|max:2', // Maksimum 2 dodatna koautora!
            'autori.*'    => 'exists:korisnik,ZapID',
        ]);

        // Provera uloge koautora
        if ($request->has('autori') && !empty($request->autori)) {
            $validniIstrazivaciCount = User::whereIn('ZapID', $request->autori) //brojač koji proverava za svakog
                ->whereHas('uloge', function($q) {
                    $q->where('uloga.UlogaID', Uloga::ISTRAZIVAC); //Da li je navedeni autor istraživač
                })->count(); //Izbroj broj istraživača

            if ($validniIstrazivaciCount !== count($request->autori)) { //Da li se poklapa broj navedenih koautora i broj koautora koji su istraživači
                return response()->json([
                    'error' => 'Svi koautori moraju imati ulogu Istraživač.'
                ], 422);
            }
        }

        //Kreiranje rada
        $naucniRad = NaucniRad::create($validatedData);

        // Povezivanje oblasti sa radom
        $naucniRad->oblasti()->attach($request->oblasti);

        // Povezivanje autora (Ulogovani + koautori)
        $sviAutori = array_unique(array_merge([auth()->id()], $request->autori ?? [])); //Možda su koautori prazan niz
        $naucniRad->autori()->attach($sviAutori); //Povezujemo autore sa radom (punimo koautorstvo tabelu)

        // Nasumična dodela recenzenta
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

        $naucniRad->load(['oblasti', 'status', 'autori']);

        return response()->json([
            'poruka' => 'Rad uspešno dodat i dodeljen recenzentu.',
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

    public function mojiRadovi()
    {
        // 1. Uzimamo trenutno ulogovanog korisnika (Istraživača)
        $korisnik = Auth::user();

        // 2. Preko relacije 'naucniRadovi' izvlačimo sve njegove radove
        // Koristimo 'with' da odmah povučemo i oblasti i status
        $radovi = $korisnik->naucniRadovi()
                        ->with(['oblasti', 'status']) // Učitaj relacije za bolji prikaz
                        ->orderBy('godina', 'desc') // Najnoviji radovi prvi
                        ->get();

        // 3. Vraćamo ih kroz Resource
        return NaucniRadResource::collection($radovi);
    }

    //Funkcija koja prikazuje sve recenzije i stavke recenzije nekog rada.
    public function prikaziRecenziju($id)
    {
        // Učitavamo rad i SVE njegove recenzije, njihove stavke i autore tih recenzija
        $rad = NaucniRad::with(['status', 'recenzije.stavke', 'recenzije.korisnik'])->find($id);

        // Ukoliko sistem ne može da pronađe rad preko NRID-a
        if (!$rad) {
            return response()->json(['message' => 'Rad nije pronađen.'], 404);
        }

        // Provera da li je ulogovani korisnik autor rada
        if (!$rad->autori->contains('ZapID', Auth::id())) {
            return response()->json(['message' => 'Niste autor ovog rada.'], 403);
        }

        // Proveravamo da li uopšte ima recenzija u nizu
        if ($rad->recenzije->isEmpty()) {
            return response()->json([
                'naslov' => $rad->naslov,
                'message' => 'Još uvek nema urađenih recenzija za ovaj rad.'
            ], 200);
        }
        //Ovde zapravo vraćamo sve recenzije i njihove stavke
        return response()->json([
            'rad_naslov' => $rad->naslov,
            'status' => $rad->status->Naziv,
            'sve_recenzije' => $rad->recenzije->map(function($recenzija) {
                return [
                    'datum' => $recenzija->Datum,
                    'recenzent' => $recenzija->korisnik->ImePrezime ?? 'Anonimni recenzent',
                    'stavke_detaljno' => $recenzija->stavke, // Sve stavke te konkretne recenzije
                ];
            })
        ]);
    }

    public function objavljeniRadovi(Request $request)
    {
        $query = NaucniRad::with(['autori', 'oblasti']) //With predstavlja eager loading za veze
            ->where('StatusID', 3); // Samo objavljeni radovi

        //Filtriramo po oblasti po ID
        if ($request->has('oblast_id')) {
            $query->whereHas('oblasti', function($q) use ($request) { // whereHas pretražuje kroz relaciju (Many-to-Many)
                // Filtriramo po ID-u oblasti u pivot tabeli 'OblastiRada'
                $q->where('Oblast.OblastID', $request->oblast_id);
            });
        }
        
        //Filter za autore (Preko ID-a korisnika/autora)
        if ($request->has('autor_id')) {
            $query->whereHas('autori', function($q) use ($request) {
                $q->where('Autorstvo.ZapID', $request->autor_id); //Tražimo u Autorstvo tabeli samo radove koji imaju ovog autora
            });
        }
        //Filter za ključne reči
        if ($request->has('keyword')) {
        $s = $request->query('keyword');
        // Koristimo % pre i posle stringa da bismo našli pojam bilo gde u tekstu
        $query->where('kljucneReci', 'LIKE', '%' . $s . '%');
        }

        $query->orderBy('godina', 'desc'); //Sortiramo da bude prvo najnoviji rad

        $radovi = $query->get(); //Izvršavamo upit

        // Ako je lista radova iz odabrane oblasti prazna
        if ($radovi->isEmpty()) {
        return response()->json([
            'success' => true,
            'message' => 'Nažalost, trenutno nema objavljenih radova sa takvim parametrima.',
            'data' => [] // Vraćamo prazan niz
        ], 200);
        }
        return NaucniRadResource::collection($radovi); //Koristimo Resource da bismo kontrolisali prikaz naučnog rada
    }
}