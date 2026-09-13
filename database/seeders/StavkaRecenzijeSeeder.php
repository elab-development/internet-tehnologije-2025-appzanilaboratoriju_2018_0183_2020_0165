<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Recenzija;
use App\Models\StavkaRecenzije;
use App\Models\Status;

class StavkaRecenzijeSeeder extends Seeder
{

    public function run(): void
    {
        Recenzija::with('naucniRad')->get()->each(function ($recenzija) {
            $rad = $recenzija->naucniRad;

            if (!$rad) {
                return;
            }

            $ishodi = $this->putDoOdluke($rad->StatusID);

            foreach (array_values($ishodi) as $redni => $statusId) {
                $datum = now()->subDays((count($ishodi) - $redni) * 9);

                StavkaRecenzije::factory()->create([
                    'RecenzijaID' => $recenzija->RecenzijaID,
                    'StatusID'    => $statusId,
                    'created_at'  => $datum,
                    'updated_at'  => $datum,
                ]);
            }
        });
    }

    private function putDoOdluke(int $statusRada): array
    {
        if ($statusRada === Status::CEKA_RECENZIJU) {
            return [];
        }

        if ($statusRada === Status::NACRT) {
            return [Status::NACRT];
        }

        if (rand(1, 100) <= 40) {
            return [Status::NACRT, $statusRada];
        }

        return [$statusRada];
    }
}
