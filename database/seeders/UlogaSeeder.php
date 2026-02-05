<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Uloga;

class UlogaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $uloge = [
            ['naziv' => 'Administrator'],
            ['naziv' => 'Recenzent'],
            ['naziv' => 'Istraživač'],
        ];

        foreach ($uloge as $uloga) {
            Uloga::updateOrCreate(['naziv' => $uloga['naziv']], $uloga);
        }
    }
}
