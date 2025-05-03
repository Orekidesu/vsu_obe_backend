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
        Schema::create('tla_methods', function (Blueprint $table) {
            $table->id();
            $table->foreignId('co_id')->constrained('course_outcomes')->cascadeOnDelete();
            $table->json('teaching_methods')->nullable();
            $table->json('learning_resources')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tla_methods');
    }
};