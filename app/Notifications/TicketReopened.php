<?php

namespace App\Notifications;

use App\Models\Ticket;
use App\Models\TicketReply;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;

class TicketReopened extends Notification implements ShouldQueue
{
    use Queueable;

    public Ticket $ticket;
    public TicketReply $reopeningReply;

    public function __construct(Ticket $ticket, TicketReply $reopeningReply)
    {
        $this->ticket = $ticket;
        $this->reopeningReply = $reopeningReply;
    }

    public function via(object $notifiable): array
    {
        // $notifiable will be the assigned agent
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $client = $this->reopeningReply->user;
        $url = route('agent.tickets.show', $this->ticket);

        return (new MailMessage)
            ->subject("Ticket Reopened: #" . $this->ticket->id)
            ->level('warning') // Sets the email color to indicate importance
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line("A ticket previously assigned to you has been reopened by the client, **{$client->name}**.")
            ->line('**Ticket:** ' . $this->ticket->title)
            ->line('**Client\'s Comment:**')
            ->line('> *' . Str::limit(strip_tags($this->reopeningReply->body), 200) . '*')
            ->action('View Reopened Ticket', $url);
    }

    public function toArray(object $notifiable): array
    {
        return [
            'ticket_id' => $this->ticket->id,
            'title' => $this->ticket->title,
            'message' => "Ticket #{$this->ticket->id} was reopened by the client.",
            'url' => route('agent.tickets.show', $this->ticket->id),
            'icon' => 'fas fa-exclamation-circle', // Example FontAwesome icon
        ];
    }
}
