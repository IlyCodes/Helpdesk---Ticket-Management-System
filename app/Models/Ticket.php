<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes; // If using soft deletes

class Ticket extends Model
{
    use HasFactory, SoftDeletes; // Add SoftDeletes if you included it in migration

    protected $fillable = [
        'user_id',
        'assigned_agent_id',
        'category_id',
        'status_id',
        'title',
        'description',
        'priority',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime', // If using soft deletes
    ];

    // Relationships
    public function user() // Client who submitted
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function agent() // Agent assigned
    {
        return $this->belongsTo(User::class, 'assigned_agent_id');
    }

    public function category()
    {
        return $this->belongsTo(TicketCategory::class, 'category_id');
    }

    public function status()
    {
        return $this->belongsTo(TicketStatus::class, 'status_id');
    }

    public function attachments()
    {
        return $this->hasMany(TicketAttachment::class);
    }

    public function replies()
    {
        return $this->hasMany(TicketReply::class)->orderBy('created_at', 'asc');
    }

    public function assignmentLogs()
    {
        return $this->hasMany(TicketAssignmentLog::class);
    }

    // Scope to get tickets for a specific user (client)
    public function scopeForUser($query, User $user)
    {
        return $query->where('user_id', $user->id);
    }

    // Scope to get tickets assigned to a specific agent
    public function scopeAssignedTo($query, User $agent)
    {
        return $query->where('assigned_agent_id', $agent->id);
    }
}
