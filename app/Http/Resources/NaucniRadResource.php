<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class NaucniRadResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->NRID,
            'naslov' => $this->naslov,
            'abstrakt' => $this->abstrakt,
            'kljucneReci' => $this->kljucneReci,
            'godina' => $this->godina,
            'oblasti' => $this->oblasti->pluck('naziv'), 
            'status' => $this->status->Naziv,
            'autori' => $this->autori->pluck('ImePrezime'),
        ];
    }
}