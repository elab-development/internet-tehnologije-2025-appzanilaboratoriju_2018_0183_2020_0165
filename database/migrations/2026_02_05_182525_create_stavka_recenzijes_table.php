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
        Schema::create('stavka_recenzije', function (Blueprint $table) {
            $table->id('StavkaID');
            $table->foreignId('RecenzijaID')->constrained('recenzija', 'RecenzijaID');
            $table->text('Komentar'); 
            $table->foreignId('StatusID')->constrained('status', 'StatusID');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stavka_recenzijes');
    }
};
