<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Status;

class StavkaRecenzijeFactory extends Factory
{

    public function definition(): array
    {
        return [
            'Komentar' => $this->faker->sentence(),
            'StatusID' => Status::inRandomOrder()->first()->StatusID,
        ];
    }
}
