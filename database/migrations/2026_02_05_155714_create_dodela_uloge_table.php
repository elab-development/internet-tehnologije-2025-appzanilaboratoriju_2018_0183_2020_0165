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
        Schema::create('dodela_uloge', function (Blueprint $table) {
        $table->id();
        $table->foreignId('ZapID')->constrained('korisnik', 'ZapID')->onDelete('cascade');
        $table->foreignId('UlogaID')->constrained('uloga', 'UlogaID')->onDelete('cascade');
        $table->date('Datum'); 
        $table->timestamps();
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dodela_uloge');
    }
};
