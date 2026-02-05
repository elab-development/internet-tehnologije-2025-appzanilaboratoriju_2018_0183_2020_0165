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
        Schema::create('OblastiRada', function (Blueprint $table) {
            $table->id(); //opcionalno
            $table->unsignedBigInteger('NRID');
            $table->unsignedBigInteger('oblastId');
            $table->timestamps();

            $table->foreign('NRID')->references('NRID')->on('NaucniRad')->onDelete('cascade');
            $table->foreign('oblastId')->references('oblastId')->on('Oblast')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('OblastiRada');
    }
};
