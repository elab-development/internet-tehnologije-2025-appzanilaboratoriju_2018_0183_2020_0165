<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Recenzija;
use App\Models\User;
use App\Models\NaucniRad;

class RecenzijaFactory extends Factory
{

    public function definition(): array
    {
        return [
            'ZapID' => User::inRandomOrder()->first()->ZapID,
            'NRID'  => NaucniRad::inRandomOrder()->first()->NRID,
            'Datum' => now(),        ];
    }
}
