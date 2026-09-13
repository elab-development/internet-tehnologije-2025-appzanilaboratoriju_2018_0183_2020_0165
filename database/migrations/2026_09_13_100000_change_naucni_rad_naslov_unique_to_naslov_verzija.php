<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void
    {
        Schema::table('NaucniRad', function (Blueprint $table) {
            $table->dropUnique('naucnirad_naslov_unique');
            $table->unique(['naslov', 'verzija'], 'naucnirad_naslov_verzija_unique');
        });
    }

    public function down(): void
    {
        Schema::table('NaucniRad', function (Blueprint $table) {
            $table->dropUnique('naucnirad_naslov_verzija_unique');
            $table->unique('naslov', 'naucnirad_naslov_unique');
        });
    }
};
