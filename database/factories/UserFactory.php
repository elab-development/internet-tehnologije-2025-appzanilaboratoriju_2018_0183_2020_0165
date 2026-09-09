<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Models\User;
use App\Models\Uloga;

class UserFactory extends Factory
{

    protected static ?string $password;

    public function definition(): array
    {
        return [
            'ImePrezime' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'password' => 'password',
            'Biografija' => $this->faker->paragraph(2),
        ];
    }

public function configure()
    {
        return $this->afterCreating(function (User $user) {
            $ids = Uloga::pluck('UlogaID')->toArray();
            if (!empty($ids)) {
                shuffle($ids);
                $user->uloge()->attach(array_slice($ids, 0, rand(1, count($ids))), [
                    'Datum' => now()
                ]);
            }
        });
    }

    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}
