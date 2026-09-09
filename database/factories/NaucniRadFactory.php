<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Status;
use App\Models\Oblast;

class NaucniRadFactory extends Factory
{

     public function definition(): array
    {
        return [
            'naslov'      => $this->faker->sentence(6),
            'abstrakt'    => $this->faker->paragraph(4),
            'kljucneReci' => implode(', ', $this->faker->words(3)),
            'godina'      => $this->faker->numberBetween(2015, 2025),
            'grupaId'     => null,
            'verzija'     => 1,
            'StatusID'    => Status::inRandomOrder()->first()->StatusID,
        ];
    }

    public function verzija(int $grupaId, int $verzija)
    {
        return $this->state(fn () => [
            'grupaId' => $grupaId,
            'verzija' => $verzija,
        ]);
    }

    public function configure()
    {
        return $this->afterCreating(function ($rad) {
            $oblasti = Oblast::inRandomOrder()
                ->limit(rand(1, 3))
                ->pluck('oblastId');

            $rad->oblasti()->attach($oblasti);
        });
    }
}
