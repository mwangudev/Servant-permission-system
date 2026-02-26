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
        Schema::table('users', function (Blueprint $table) {
            // Add 'assigned_as' after the 'role' column
            $table->string('assigned_as', 100)
                  ->nullable()
                  ->after('role')
                  ->comment('e.g. HOD, Supervisor, HR, etc.');

            // Replace or improve the existing 'status' column
            // (assuming there is already a 'status' column)
            // If there is NO existing 'status' column yet → use addColumn instead of modify
            $table->enum('status', ['inactive', 'active', 'retired', 'deceased'])
                  ->default('active')
                  ->nullable(false)
                  ->after('assigned_as')
                  ->comment('User account status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Drop the columns we added (in reverse order)
            $table->dropColumn('status');       // drop the new/modified status
            $table->dropColumn('assigned_as');  // drop assigned_as
        });
    }
};
