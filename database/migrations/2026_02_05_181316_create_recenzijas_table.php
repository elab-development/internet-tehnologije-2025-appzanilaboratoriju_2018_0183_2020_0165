<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('recenzija', function (Blueprint $table) {
            $table->id('RecenzijaID');
            $table->date('Datum');
            $table->foreignId('ZapID')->constrained('korisnik', 'ZapID')->onDelete('cascade');

            $table->foreignId('NRID')->constrained('NaucniRad', 'NRID')->onDelete('cascade');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recenzijas');
    }
};
