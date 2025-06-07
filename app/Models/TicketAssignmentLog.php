<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TicketAssignmentLog extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'ticket_assignment_logs';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'ticket_id',
        'admin_id',
        'assigned_agent_id',
        'previous_agent_id',
        'assigned_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'assigned_at' => 'datetime',
    ];

    /**
     * Indicates if the model should be timestamped.
     * Set to false if you only use 'assigned_at' and don't have 'created_at'/'updated_at' columns.
     * If you added $table->timestamps() in the migration, keep this as true (or remove the line, as true is default).
     *
     * @var bool
     */
    public $timestamps = false; // Set to true if you added $table->timestamps() in the migration

    /**
     * Get the ticket associated with the assignment log.
     */
    public function ticket()
    {
        return $this->belongsTo(Ticket::class);
    }

    /**
     * Get the admin who performed the assignment.
     */
    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    /**
     * Get the agent who was assigned the ticket.
     */
    public function assignedAgent()
    {
        return $this->belongsTo(User::class, 'assigned_agent_id');
    }

    /**
     * Get the previously assigned agent, if any.
     */
    public function previousAgent()
    {
        return $this->belongsTo(User::class, 'previous_agent_id');
    }
}
