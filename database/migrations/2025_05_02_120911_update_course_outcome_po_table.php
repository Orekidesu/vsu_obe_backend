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
        Schema::table('course_outcome_po', function (Blueprint $table) {
            //
            $table->enum('ied', ['I', 'E', 'D'])->after('po_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('course_outcome_po', function (Blueprint $table) {
            //
            $table->dropColumn('ied');
        });
    }
};