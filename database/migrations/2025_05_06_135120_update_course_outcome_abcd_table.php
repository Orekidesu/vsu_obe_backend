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
        Schema::table('course_outcome_abcd', function (Blueprint $table) {
            //
            $table->renameColumn('behaviour', 'behavior');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('course_outcome_abcd', function (Blueprint $table) {
            //
            $table->renameColumn('behavior', 'behaviour');
        });
    }
};