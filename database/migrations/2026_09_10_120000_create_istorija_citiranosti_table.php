<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('IstorijaCitiranosti', function (Blueprint $table) {
            $table->id('IstorijaID');
            $table->foreignId('NRID')->constrained('NaucniRad', 'NRID')->onDelete('cascade');
            $table->unsignedInteger('brojCitata');
            $table->date('datum');
            $table->timestamps();

            $table->unique(['NRID', 'datum']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('IstorijaCitiranosti');
    }
};
