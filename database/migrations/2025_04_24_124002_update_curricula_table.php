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
        Schema::table('curricula', function (Blueprint $table) {
            //
            $table->dropForeign(['program_id']);
            $table->dropColumn('program_id');

            $table->foreignId('program_proposal_id')->after('id')->constrained('program_proposals')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('curricula', function (Blueprint $table) {
            //
            $table->dropForeign(['program_proposal_id']);
            $table->dropColumn('program_proposal_id');

            $table->foreignId('program_id')->constrained('programs')->cascadeOnDelete();
        });
    }
};
