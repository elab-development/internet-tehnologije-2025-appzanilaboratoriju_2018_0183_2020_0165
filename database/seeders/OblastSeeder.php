<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Oblast;

class OblastSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $oblasti = [
            ['naziv' => 'Matematika'],
            ['naziv' => 'Informacione Tehnologije'],
            ['naziv' => 'Biologija'],
            ['naziv' => 'Medicina'],
            ['naziv' => 'Fizika'],
            ['naziv' => 'Veštačka inteligencija']
        ];

        foreach ($oblasti as $oblast) {
            Oblast::updateOrCreate(['naziv' => $oblast['naziv']], $oblast);
        }
    }
}
