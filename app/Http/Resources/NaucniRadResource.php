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
            'autori' => $this->autori->pluck('ImePrezime'),
            'spoljniAutori' => $this->spoljniAutori,
        ];
    }
}
