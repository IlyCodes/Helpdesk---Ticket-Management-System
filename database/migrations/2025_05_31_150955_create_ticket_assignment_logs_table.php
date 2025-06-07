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
        Schema::create('ticket_assignment_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ticket_id')->constrained('tickets')->onDelete('cascade');
            $table->foreignId('admin_id')->constrained('users')->onDelete('cascade'); // User (Admin) who performed the assignment
            $table->foreignId('assigned_agent_id')->constrained('users')->onDelete('cascade'); // User (Agent) who was assigned
            $table->foreignId('previous_agent_id')->nullable()->constrained('users')->onDelete('set null'); // Previously assigned agent, if any
            $table->timestamp('assigned_at')->useCurrent(); // When the assignment occurred
            // No separate timestamps() here if 'assigned_at' covers the creation time of the log entry.
            // If you want distinct created_at/updated_at for the log record itself, add $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ticket_assignment_logs');
    }
};