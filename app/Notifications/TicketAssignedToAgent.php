<?php

namespace App\Notifications;

use App\Models\Ticket;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;

class TicketAssignedToAgent extends Notification implements ShouldQueue
{
    use Queueable;

    public Ticket $ticket;

    /**
     * Create a new notification instance.
     */
    public function __construct(Ticket $ticket)
    {
        $this->ticket = $ticket;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        // $notifiable here is the Agent User model
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $url = route('agent.tickets.show', $this->ticket);

        return (new MailMessage)
            ->subject('New Ticket Assigned: #' . $this->ticket->id)
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line('A new support ticket has been assigned to you.')
            ->line('Ticket ID: #' . $this->ticket->id)
            ->line('Title: ' . $this->ticket->title)
            ->line('Client: ' . $this->ticket->user->name)
            ->action('View Assigned Ticket', $url)
            ->line('Please review and respond at your earliest convenience.');
    }

    /**
     * Get the array representation of the notification.
     * (For database/in-app channel)
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'ticket_id' => $this->ticket->id,
            'title' => $this->ticket->title,
            'message' => 'New ticket #' . $this->ticket->id . ' ("' . Str::limit($this->ticket->title, 30) . '") has been assigned to you.',
            'url' => route('agent.tickets.show', $this->ticket->id),
            'icon' => 'fas fa-user-tie', // Example FontAwesome icon
        ];
    }
}
