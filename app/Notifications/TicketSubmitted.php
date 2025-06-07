<?php

namespace App\Notifications;

use App\Models\Ticket;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TicketSubmitted extends Notification implements ShouldQueue
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
        return ['mail', 'database']; // Send via email and store in DB
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $url = route('client.tickets.show', $this->ticket);

        return (new MailMessage)
            ->subject('Support Ticket Submitted: #' . $this->ticket->id)
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line('Thank you for submitting a support ticket. We have received your request and will process it shortly.')
            ->line('Ticket ID: #' . $this->ticket->id)
            ->line('Title: ' . $this->ticket->title)
            ->action('View Ticket', $url)
            ->line('Thank you for using our application!');
    }

    /**
     * Get the array representation of the notification.
     * (For database channel)
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'ticket_id' => $this->ticket->id,
            'title' => $this->ticket->title,
            'message' => 'Your support ticket #' . $this->ticket->id . ' has been submitted.',
            'url' => route('client.tickets.show', $this->ticket->id),
            'icon' => 'fas fa-ticket-alt', // Example FontAwesome icon
        ];
    }
}
