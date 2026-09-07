<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('StavkaRecenzije', function (Blueprint $table) {
            $table->dropForeign('stavka_recenzije_recenzijaid_foreign');
            $table->foreign('RecenzijaID')
                  ->references('RecenzijaID')
                  ->on('recenzija')
                  ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('StavkaRecenzije', function (Blueprint $table) {
            $table->dropForeign(['RecenzijaID']);
            $table->foreign('RecenzijaID')
                  ->references('RecenzijaID')
                  ->on('recenzija')
                  ->onDelete('restrict');
        });
    }
};
