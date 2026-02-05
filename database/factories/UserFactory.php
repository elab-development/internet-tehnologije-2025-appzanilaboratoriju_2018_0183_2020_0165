<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Models\User;
use App\Models\Uloga;
/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
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
            $ids = Uloga::pluck('UlogaID')->toArray(); // uzmi sve ID-eve
            if (!empty($ids)) {
                shuffle($ids); // random redosled
                $user->uloge()->attach(array_slice($ids, 0, rand(1, count($ids))), [
                    'Datum' => now()
                ]);
            }
        });
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}
