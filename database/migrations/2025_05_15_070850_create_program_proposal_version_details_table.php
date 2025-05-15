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
        Schema::create('program_proposal_version_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('program_proposal_version_id')->constrained('program_proposal_versions')->cascadeOnDelete()->name('fk_ppvd_version_id');
            $table->string('section');
            $table->foreignId('curriculum_course_id')->nullable()->constrained('curriculum_courses')->cascadeOnDelete();
            $table->json('previous_data')->nullable();
            $table->json('new_data')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('program_proposal_version_details');
    }
};
