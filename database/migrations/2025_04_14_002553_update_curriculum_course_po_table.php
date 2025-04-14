<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Use a different column name to avoid conflicts
        Schema::table('curriculum_course_po', function (Blueprint $table) {
            // Add the new JSON column with a different name
            $table->json('ird_values')->after('po_id')->nullable();
        });

        // Now drop the old column
        Schema::table('curriculum_course_po', function (Blueprint $table) {
            $table->dropColumn('ird');
        });

        Schema::table('curriculum_course_po', function (Blueprint $table) {
            $table->renameColumn('ird_values', 'ird');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // First add back the original enum column
        Schema::table('curriculum_course_po', function (Blueprint $table) {
            $table->enum('ird_original', ['I', 'R', 'D'])->after('po_id')->nullable();
        });

        // Drop the JSON column
        Schema::table('curriculum_course_po', function (Blueprint $table) {
            $table->dropColumn('ird_values');
        });

        // Rename the column back to original name
        Schema::table('curriculum_course_po', function (Blueprint $table) {
            $table->renameColumn('ird_original', 'ird');
        });
    }
};