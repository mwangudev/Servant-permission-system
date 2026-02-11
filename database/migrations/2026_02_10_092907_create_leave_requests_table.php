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
        Schema::create('leave_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('request_type'); // e.g., sick, annual
            $table->date('start_date');
            $table->date('end_date');
            $table->enum('status', ['submitted', 'pending','on_progress', 'approved', 'rejected'])->default('submitted');
            $table->string('report_path')->nullable(); // optional file path
            $table->text('admin_remark')->nullable(); // admin comments
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leave_requests');
    }
};
