<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

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
            'naslov' => $this->faker->sentence(6),
            'abstrakt' => $this->faker->paragraph(3),
            'godina' => $this->faker->year(),
            'grupaId' => $this->faker->numberBetween(1, 100),
            'verzija' => $this->faker->randomElement(['1.0','2.0','3.0']),
        ];
    }
}
