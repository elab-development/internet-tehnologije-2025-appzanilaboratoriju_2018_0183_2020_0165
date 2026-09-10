<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RecenzijaResource extends JsonResource
{

    public function toArray(Request $request): array
    {
        return [
                'id' => $this->RecenzijaID,
                'datumDodele' => $this->Datum,
                'naucniRad' => new NaucniRadResource($this->whenLoaded('naucniRad')),
                'stavke' => $this->whenLoaded('stavke', function () {
                    return $this->stavke
                        ->sortByDesc('created_at')
                        ->map(function ($stavka) {
                            return [
                                'id' => $stavka->StavkaID,
                                'komentar' => $stavka->Komentar,
                                'status' => $stavka->status->Naziv ?? null,
                                'datum' => $stavka->created_at,
                            ];
                        })
                        ->values();
                }),
            ];
    }
}
