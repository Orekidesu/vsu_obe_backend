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
        Schema::table('semesters', function (Blueprint $table) {

            //
            $table->integer('year')->after('id');
            $table->dropColumn('name');
            $table->enum('sem', ['first', 'second', 'midyear'])->after('year');

            $table->unique(['year', 'sem']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('semesters', function (Blueprint $table) {
            //
            $table->dropUnique(['year', 'sem']);
            $table->dropColumn('year');
            $table->dropColumn('sem');
            $table->string('name')->after('id');
        });
    }
};
