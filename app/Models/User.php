<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens; // If using Sanctum for APIs

class User extends Authenticatable implements MustVerifyEmail // Add MustVerifyEmail if you want email verification
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'specialization',
        'is_active',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    /**
     * Check if the user has any of the given roles.
     *
     * @param array $roles
     * @return bool
     */
    public function hasAnyRole(array $roles): bool
    {
        return in_array($this->role, $roles);
    }

    /**
     * Check if the user is an admin.
     *
     * @return bool
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /**
     * Check if the user is an agent.
     *
     * @return bool
     */
    public function isAgent(): bool
    {
        return $this->role === 'agent';
    }

    /**
     * Check if the user is a client.
     *
     * @return bool
     */
    public function isClient(): bool
    {
        return $this->role === 'client';
    }

    // Relationships
    public function tickets()
    {
        return $this->hasMany(Ticket::class); // Tickets created by this user (client)
    }

    public function assignedTickets()
    {
        return $this->hasMany(Ticket::class, 'assigned_agent_id'); // Tickets assigned to this user (agent)
    }

    public function ticketReplies()
    {
        return $this->hasMany(TicketReply::class);
    }

    public function assignmentLogsAsAdmin() // Logs where this user was the admin who assigned
    {
        return $this->hasMany(TicketAssignmentLog::class, 'admin_id');
    }

    public function assignmentLogsAsAgent() // Logs where this user was the agent assigned
    {
        return $this->hasMany(TicketAssignmentLog::class, 'assigned_agent_id');
    }
}
