<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('NaucniRad', function (Blueprint $table) {
            $table->string('putanjaFajla')->nullable()->after('spoljniAutori');
            $table->string('imeFajla')->nullable()->after('putanjaFajla');
        });
    }

    public function down(): void
    {
        Schema::table('NaucniRad', function (Blueprint $table) {
            $table->dropColumn(['putanjaFajla', 'imeFajla']);
        });
    }
};
