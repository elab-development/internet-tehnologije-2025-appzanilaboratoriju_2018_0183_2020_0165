<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class NaucniRadResource extends JsonResource
{

    public function toArray(Request $request): array
    {
        return [
            'id' => $this->NRID,
            'naslov' => $this->naslov,
            'abstrakt' => $this->abstrakt,
            'kljucneReci' => $this->kljucneReci,
            'godina' => $this->godina,
            'doi' => $this->DOI,
            'oblasti' => $this->oblasti->pluck('naziv'),
            'status' => $this->status->Naziv,
            'autori' => $this->autori->map(function ($autor) {
                return [
                    'id'         => $autor->ZapID,
                    'imePrezime' => $autor->ImePrezime,
                ];
            })->values(),
            'recenzenti' => $this->whenLoaded('recenzije', function () {
                return $this->recenzije
                    ->filter(fn ($recenzija) => $recenzija->korisnik !== null)
                    ->map(function ($recenzija) {
                        return [
                            'recenzijaId' => $recenzija->RecenzijaID,
                            'id'          => $recenzija->korisnik->ZapID,
                            'imePrezime'  => $recenzija->korisnik->ImePrezime,
                        ];
                    })
                    ->values();
            }),
            'spoljniAutori' => $this->spoljniAutori,
            'verzija' => $this->verzija,
            'grupaId' => $this->grupaId,
            'imaFajl' => !empty($this->putanjaFajla),
            'imeFajla' => $this->imeFajla,
        ];
    }
}
