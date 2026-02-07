<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Status;
/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\StavkaRecenzije>
 */
class StavkaRecenzijeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'Komentar' => $this->faker->sentence(),
            'StatusID' => Status::inRandomOrder()->first()->StatusID,
        ];
    }
}
