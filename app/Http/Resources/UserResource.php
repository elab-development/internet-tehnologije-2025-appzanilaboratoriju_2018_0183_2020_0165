<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->ZapID,
            'imePrezime' => $this->ImePrezime,
            'email' => $this->email,
            'biografija' => $this->Biografija,

            'uloge' => $this->uloge->map(function ($uloga) {
                return [
                    'id' => $uloga->UlogaID,
                    'naziv' => $uloga->Naziv,
                    'datumDodele' => $uloga->pivot->Datum,
                ];
            }),
        ];
    }
}