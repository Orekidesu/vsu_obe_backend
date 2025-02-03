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
        //
        Schema::table('users', function (Blueprint $table)
            {
                $table->foreignId('role_id')->constrained('roles')->onDelete('cascade');
                $table->foreignId('college_id')->constrained('colleges')->onDelete('cascade');
                $table->foreignId('department_id')->constrained('departments')->onDelete('cascade');
            }
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
        Schema::table('users',function(Blueprint $table)
            {
                $table->dropForeign(['role_id']);
                $table->dropColumn(['role_id']);

                $table->dropForeign(['college_id']);
                $table->dropColumn(['college_id']);
                
                $table->dropForeign(['department_id']);
                $table->dropColumn(['department_id']);
            }

        );
        
    }
};
