<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RecenzijaResource extends JsonResource
{

    public function toArray(Request $request): array
    {
        return [
                'RecenzijaID' => $this->RecenzijaID,
                'DatumDodele' => $this->Datum,
                'naucniRad' => new NaucniRadResource($this->whenLoaded('naucniRad')),
                'stavke' => $this->whenLoaded('stavke'),
            ];
    }
}
