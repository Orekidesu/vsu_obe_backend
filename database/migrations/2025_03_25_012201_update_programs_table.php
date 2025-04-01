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
        Schema::table('programs', function (Blueprint $table) {
            // Drop unique indexes if they exist
            $table->dropUnique(['name']);
            $table->dropUnique(['abbreviation']);
            // 
            $table->string('name')->nullable()->change();
            $table->string('abbreviation')->nullable()->change();
            //
            $table->integer('version')->default(1);
            $table->enum('status', ['active', 'pending', 'archived'])->default('pending');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('programs', function (Blueprint $table) {
            //
            $table->dropColumn('version');
            $table->dropColumn('status');
            // 
            $table->string('name')->nullable(false)->change();
            $table->string('abbreviation')->nullable(false)->change();
        });
    }
};
