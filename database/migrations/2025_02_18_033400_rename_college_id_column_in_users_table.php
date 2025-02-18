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
        Schema::table('users',function(Blueprint $table){
           
            // Drop foreign key constraint
            $table->dropForeign(['college_id']);
            
            // Remane the column
            $table->renameColumn('college_id','faculty_id');

            // Re add foreign key constraint

            $table->foreign('faculty_id')->references('id')->on('faculties')->onDelete('cascade');




        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users',function(Blueprint $table){

             // Drop foreign key constraint
            $table->dropForeign(['faculty_id']);
            
            // Remane the column
            $table->renameColumn('faculty_id','college_id');

            // Re add foreign key constraint

            $table->foreign('college_id')->references('id')->on('faculties')->onDelete('cascade');
        });
    }
};