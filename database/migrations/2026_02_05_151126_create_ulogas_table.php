<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void
    {
        Schema::create('uloga', function (Blueprint $table) {
        $table->id('UlogaID');
        $table->string('Naziv');
        $table->timestamps();
    });
    }

    public function down(): void
    {
        Schema::dropIfExists('ulogas');
    }
};
