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
        Schema::create('graduate_attribute_peo', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ga_id')->constrained('graduate_attributes')->cascadeOnDelete();
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
        Schema::dropIfExists('graduate_attribute_peo');
    }
};
