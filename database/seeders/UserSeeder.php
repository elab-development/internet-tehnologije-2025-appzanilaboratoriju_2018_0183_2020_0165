<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Uloga;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $testUser = User::create([
            'ImePrezime' => 'Test Admin',
            'email' => 'admin@gmail.com',
            'password' => Hash::make('123'),
            'Biografija' => 'Ovo je test nalog za postman.'
        ]);
        $testUser->uloge()->attach(1, [
            'datum' => Carbon::now()
        ]); 

        //istrazivac
        $istrazivac = User::create([
            'ImePrezime' => 'Test Istrazivac',
            'email' => 'istrazivac@gmail.com',
            'password' => Hash::make('123'),
            'Biografija' => 'Test nalog za istrazivaca.'
        ]);

        $istrazivac->uloge()->attach(3, [
            'datum' => Carbon::now()
        ]);

         $recenzent = User::create([
            'ImePrezime' => 'Test Recenzent',
            'email' => 'recenzent@gmail.com',
            'password' => Hash::make('123'),
            'Biografija' => 'Test nalog za recenzenta.'
        ]);

        $recenzent->uloge()->attach(2, [
            'datum' => Carbon::now()
        ]);




        User::factory(10)->create();
    }
}