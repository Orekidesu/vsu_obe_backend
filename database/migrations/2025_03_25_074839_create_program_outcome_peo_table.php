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
        Schema::create('program_outcome_peo', function (Blueprint $table) {
            $table->id();
            $table->foreignId('po_id')->constrained('program_outcomes')->cascadeOnDelete();
            $table->foreignId('peo_id')->constrained('program_educational_objectives')->cascadeOnDelete();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('program_outcome_peo');
    }
};
