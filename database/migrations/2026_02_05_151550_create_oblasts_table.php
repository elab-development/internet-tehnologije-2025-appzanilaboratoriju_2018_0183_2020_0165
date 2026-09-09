<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void
    {
         Schema::create('Oblast', function (Blueprint $table) {
            $table->id('oblastId');
            $table->string('naziv');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('Oblast');
    }
};
