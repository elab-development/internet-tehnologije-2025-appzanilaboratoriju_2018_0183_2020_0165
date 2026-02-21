<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RecenzijaResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'recenzija_id' => $this->RecenzijaID,
            'datum' => $this->Datum,
            
            // Podaci o naučnom radu
            'naucni_rad' => new NaucniRadResource($this->whenLoaded('naucniRad')),

            // Podaci o recenzentu
            'recenzent' => [
                'ime_prezime' => $this->korisnik->ImePrezime ?? 'Nepoznato',
            ],

            // Lista STAVKI
            'stavke' => $this->whenLoaded('stavke', function() {
                return $this->stavke->map(function($stavka) {
                    return [
                        'stavka_id' => $stavka->StavkaID,
                        'komentar' => $stavka->Komentar,
                        'nov_status' => $stavka->status->Naziv ?? 'Nema statusa',
                    ];
                });
            }),
        ];
    }
}
