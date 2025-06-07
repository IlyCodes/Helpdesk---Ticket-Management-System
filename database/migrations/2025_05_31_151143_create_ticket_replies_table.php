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
        Schema::create('ticket_replies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ticket_id')
                ->constrained('tickets') // Assumes your tickets table is named 'tickets'
                ->onDelete('cascade');   // If a ticket is deleted, its replies are also deleted

            $table->foreignId('user_id')
                ->constrained('users')   // Assumes your users table is named 'users'
                ->onDelete('cascade');   // If a user is deleted, their replies are also deleted

            $table->text('body');
            $table->boolean('is_internal')->default(false); // For agent/admin internal notes
            $table->timestamps(); // Adds created_at and updated_at columns
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ticket_replies');
    }
};
