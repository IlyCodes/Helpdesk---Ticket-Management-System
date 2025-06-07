<?php

namespace App\Events;

use App\Models\Ticket; // Ensure you import your Ticket model
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TicketCreated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Ticket $ticket;

    /**
     * Create a new event instance.
     *
     * @param \App\Models\Ticket $ticket
     * @return void
     */
    public function __construct(Ticket $ticket)
    {
        $this->ticket = $ticket;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        // This event is primarily for backend listeners (like AI assignment),
        // so broadcasting is likely not needed unless you have a real-time frontend update.
        // If not broadcasting, you can remove this method or return an empty array.
        return [
            // new PrivateChannel('channel-name'), // Example if broadcasting
        ];
    }
}
