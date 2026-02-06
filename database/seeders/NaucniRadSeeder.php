<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\NaucniRad;

class NaucniRadSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $brojRadova = 5; // grupe
        $maxVerzija = 4;

        for ($g = 1; $g <= $brojRadova; $g++) {
            $brojVerzija = rand(1, $maxVerzija);

            for ($v = 1; $v <= $brojVerzija; $v++) {
                NaucniRad::factory()
                    ->verzija($g, $v)
                    ->create();
            }
        }
    }
}
 