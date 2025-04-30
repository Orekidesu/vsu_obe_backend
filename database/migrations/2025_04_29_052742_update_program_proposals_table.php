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
            $table->boolean('department_revision_required')->default(false);
            $table->boolean('committee_revision_required')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('program_proposals', function (Blueprint $table) {
            $table->dropColumn('department_revision_required');
            $table->dropColumn('committee_revision_required');
        });
    }
};