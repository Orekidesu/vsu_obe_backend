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
        Schema::create('program_proposal_versions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('program_proposal_id')->constrained('program_proposals')->cascadeOnDelete();
            $table->unsignedInteger('version')->default(1);
            $table->enum('change_type', ['department', 'committee', 'both']);
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('program_proposal_versions');
    }
};
