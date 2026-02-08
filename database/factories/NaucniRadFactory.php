<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Status;
use App\Models\Oblast;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\NaucniRad>
 */
class NaucniRadFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */

     public function definition(): array
    {
        return [
            'naslov'   => $this->faker->sentence(6),
            'abstrakt' => $this->faker->paragraph(4),
            'godina'   => $this->faker->numberBetween(2015, 2025),
            'grupaId'  => null,   // namerno null, setuje se kroz state
            'verzija'  => 1,
            'StatusID' => Status::inRandomOrder()->first()->StatusID,
        ];
    }

    /**
     * State za konkretnu verziju rada
     */
    public function verzija(int $grupaId, int $verzija)
    {
        return $this->state(fn () => [
            'grupaId' => $grupaId,
            'verzija' => $verzija,
        ]);
    }

    /**
     * Nakon kreiranja rada, dodeljujemo random oblasti (pivot tabela)
     */
    public function configure()
    {
        return $this->afterCreating(function ($rad) {
            $oblasti = Oblast::inRandomOrder()
                ->limit(rand(1, 3))
                ->pluck('oblastId'); // koristi pravi PK!

            $rad->oblasti()->attach($oblasti);
        });
    }
}
