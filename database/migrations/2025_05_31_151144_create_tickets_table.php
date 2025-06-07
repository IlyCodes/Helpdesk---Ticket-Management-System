<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            // $table->uuid('id')->primary(); // Alternative: Use UUIDs
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade'); // Client who submitted
            $table->foreignId('assigned_agent_id')->nullable()->constrained('users')->onDelete('set null'); // Agent assigned
            $table->foreignId('category_id')->constrained('ticket_categories')->onDelete('restrict');
            $table->foreignId('status_id')->constrained('ticket_statuses')->onDelete('restrict');
            
            $table->string('title');
            $table->text('description');
            $table->enum('priority', ['low', 'medium', 'high'])->default('medium');
            
            $table->timestamps(); // created_at, updated_at
            $table->softDeletes(); // For soft deleting tickets if needed
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};
