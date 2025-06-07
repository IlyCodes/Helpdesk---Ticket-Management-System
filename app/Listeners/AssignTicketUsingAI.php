<?php

namespace App\Listeners;

use App\Events\TicketCreated;
use App\Models\TicketStatus;
use App\Services\TicketAssignmentService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class AssignTicketUsingAI implements ShouldQueue
{
    use InteractsWithQueue;

    public int $tries = 3;
    public int $timeout = 120;
    protected TicketAssignmentService $ticketAssignmentService;

    /**
     * Create the event listener.
     */
    public function __construct(TicketAssignmentService $ticketAssignmentService)
    {
        $this->ticketAssignmentService = $ticketAssignmentService;
    }

    /**
     * Handle the event.
     */
    public function handle(TicketCreated $event): void
    {
        $ticket = $event->ticket;

        // Initial check: if already assigned or not in 'not-assigned' status, maybe skip.
        // This logic can be fine-tuned based on business rules.
        // The service method itself doesn't re-check these conditions as it might be used for explicit re-assignment.
        $notAssignedStatus = TicketStatus::where('slug', 'not-assigned')->first();
        if ($ticket->assigned_agent_id || !$notAssignedStatus || $ticket->status_id !== $notAssignedStatus->id) {
            Log::info("Ticket #{$ticket->id} not eligible for initial AI assignment via listener (already assigned or not in 'not-assigned' status).");
            return;
        }

        $this->ticketAssignmentService->assignTicketViaAI($ticket, null); // null for assignerId as it's event-driven
    }

    /**
     * Handle a job failure.
     */
    public function failed(TicketCreated $event, \Throwable $exception): void
    {
        Log::critical("AI Ticket Assignment via Listener FAILED permanently for ticket #{$event->ticket->id}: " . $exception->getMessage());
        // Notify an administrator or implement a fallback system.
    }
}
