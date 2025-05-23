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
        Schema::table('program_proposal_revisions', function (Blueprint $table) {
            //
            $table->unsignedInteger('version')->after('program_proposal_id')->default(1);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('program_proposal_revisions', function (Blueprint $table) {
            //
            $table->dropColumn('version');
        });
    }
};
