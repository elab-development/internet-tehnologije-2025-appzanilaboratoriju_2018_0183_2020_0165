<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Status;

class StatusSeeder extends Seeder
{

    public function run(): void
    {
        $statusi = [
            ['Naziv' => 'Nacrt'],
            ['Naziv' => 'Čeka recenziju'],
            ['Naziv' => 'Objavljen'],
            ['Naziv' => 'Odbijen'],
        ];

          foreach ($statusi as $status) {
            Status::updateOrCreate(
                ['Naziv' => $status['Naziv']],
                $status
            );
          }
    }
}
