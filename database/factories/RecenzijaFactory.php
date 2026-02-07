<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Recenzija;
use App\Models\User;
use App\Models\NaucniRad;
/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Recenzija>
 */
class RecenzijaFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'ZapID' => User::inRandomOrder()->first()->ZapID,
            'NRID'  => NaucniRad::inRandomOrder()->first()->NRID,
            'Datum' => now(),        ];
    }
}
