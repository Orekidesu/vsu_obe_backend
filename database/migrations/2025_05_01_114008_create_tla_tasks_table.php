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
        Schema::create('tla_tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('co_id')->constrained('course_outcomes')->cascadeOnDelete();
            $table->string('at_code');
            $table->string('at_name');
            $table->string('at_tool');
            $table->decimal('weight', 5, 2); // e.g., 10.00 (%)
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tla_tasks');
    }
};