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
        Schema::table('program_proposals', function (Blueprint $table) {
            //
            DB::statement("ALTER TABLE program_proposals MODIFY COLUMN status ENUM('approved', 'pending', 'review', 'revision')");

            // Then update any existing 'rejected' values to 'review'
            DB::table('program_proposals')
                ->where('status', 'rejected')
                ->update(['status' => 'review']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('program_proposals', function (Blueprint $table) {
            //
            DB::table('program_proposals')
                ->where('status', 'review')
                ->update(['status' => 'rejected']);

            DB::statement("ALTER TABLE program_proposals MODIFY COLUMN status ENUM('approved', 'pending', 'rejected', 'revision')");
        });
    }
};