<?php

namespace App\Observers;

use App\Models\Ticket;
use App\Models\TicketStatus;
use App\Notifications\TicketResolvedConfirmation;
use Illuminate\Support\Facades\Log;

class TicketObserver
{
    /**
     * Handle the Ticket "created" event.
     */
    public function created(Ticket $ticket): void
    {
        //
    }

    /**
     * Handle the Ticket "updated" event.
     */
    public function updated(Ticket $ticket): void
    {
        // Check if the 'status_id' field was the one that changed.
        if ($ticket->isDirty('status_id')) {
            Log::info("Ticket #{$ticket->id} status was changed.");

            // Get the ID for the 'Resolved' status. We cache it for performance.
            $resolvedStatusId = cache()->remember('resolved_status_id', 3600, function () {
                return TicketStatus::where('slug', 'resolved')->first()?->id;
            });

            $ticket->load('status');

            // If the new status is 'Resolved', send the notification.
            if ($ticket->status_id == $resolvedStatusId) {
                if ($ticket->user) {
                    try {
                        $ticket->user->notify(new TicketResolvedConfirmation($ticket));
                        Log::info("Dispatched TicketResolvedConfirmation notification for ticket #{$ticket->id}.");
                    } catch (\Exception $e) {
                        Log::error("Failed to dispatch TicketResolvedConfirmation for ticket #{$ticket->id}: " . $e->getMessage());
                    }
                }
            }
        }
    }

    /**
     * Handle the Ticket "deleted" event.
     */
    public function deleted(Ticket $ticket): void
    {
        //
    }

    /**
     * Handle the Ticket "restored" event.
     */
    public function restored(Ticket $ticket): void
    {
        //
    }

    /**
     * Handle the Ticket "force deleted" event.
     */
    public function forceDeleted(Ticket $ticket): void
    {
        //
    }
}
