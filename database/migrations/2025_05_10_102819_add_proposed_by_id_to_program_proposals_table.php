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
        Schema::table('program_proposals', function (Blueprint $table) {
            //
            $table->foreignId('proposed_by_id')->after('comment')->nullable()->constrained('users');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('program_proposals', function (Blueprint $table) {
            //
            $table->dropForeign(['proposed_by_id']);
            $table->dropColumn('proposed_by_id');
        });
    }
};