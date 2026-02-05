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
        Schema::table('NaucniRad', function (Blueprint $table) {
            $table->foreignId('StatusID')
                  ->after('NRID') 
                  ->constrained('status', 'StatusID')
                  ->onDelete('cascade'); 
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('naucni_rad', function (Blueprint $table) {
            $table->dropForeign(['StatusID']);
            $table->dropColumn('StatusID');
        });
    }
};
