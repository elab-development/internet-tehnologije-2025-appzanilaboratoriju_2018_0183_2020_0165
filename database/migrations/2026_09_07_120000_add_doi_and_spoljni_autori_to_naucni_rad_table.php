<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void
    {
        Schema::table('NaucniRad', function (Blueprint $table) {
            $table->string('DOI')->nullable()->unique()->after('godina');
            $table->text('spoljniAutori')->nullable()->after('DOI');
        });
    }

    public function down(): void
    {
        Schema::table('NaucniRad', function (Blueprint $table) {
            $table->dropUnique(['DOI']);
            $table->dropColumn(['DOI', 'spoljniAutori']);
        });
    }
};
