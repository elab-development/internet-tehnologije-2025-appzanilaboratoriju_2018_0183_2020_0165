<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Recenzija;
use App\Models\StavkaRecenzije;



class StavkaRecenzijeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Recenzija::all()->each(function ($recenzija) {
            StavkaRecenzije::factory(rand(1, 3))->create([
                'RecenzijaID' => $recenzija->RecenzijaID,
            ]);
        });    
    }
}
