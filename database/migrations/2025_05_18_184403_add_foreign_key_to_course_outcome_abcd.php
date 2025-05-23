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
            $table->foreign('co_id')->references('id')->on('course_outcomes')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('course_outcome_abcd', function (Blueprint $table) {
            //
            $table->dropForeign(['co_id']);
        });
    }
};
