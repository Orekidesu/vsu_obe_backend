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
        Schema::create('program_proposal_revisions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('program_proposal_id')->constrained('program_proposals')->cascadeOnDelete();
            $table->enum('level', ['committee', 'department']);
            $table->foreignId('curriculum_course_id')->nullable()->constrained('curriculum_courses')->nullOnDelete();
            $table->string('section')->nullable();
            $table->text('details');

            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('program_proposal_revisions');
    }
};