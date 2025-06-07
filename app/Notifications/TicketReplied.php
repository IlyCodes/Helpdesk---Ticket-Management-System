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

class TicketReplied extends Notification implements ShouldQueue
{
    use Queueable;

    public Ticket $ticket;
    public TicketReply $reply;

    /**
     * Create a new notification instance.
     */
    public function __construct(Ticket $ticket, TicketReply $reply)
    {
        $this->ticket = $ticket;
        $this->reply = $reply;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $replier = $this->reply->user;
        $url = $notifiable->isAgent() || $notifiable->isAdmin()
            ? route('admin.tickets.show', $this->ticket)
            : route('client.tickets.show', $this->ticket);

        $greeting = 'Hello ' . $notifiable->name . ',';
        $subject = "New Reply on your Ticket: #" . $this->ticket->id;
        $line1 = "A new reply has been posted on your support ticket by {$replier->name}:";

        if ($notifiable->isAgent() || $notifiable->isAdmin()) {
            $subject = "New Reply on Ticket #" . $this->ticket->id;
            $line1 = "{$replier->name} has posted a new reply on ticket #" . $this->ticket->id;
        }

        return (new MailMessage)
            ->subject($subject)
            ->greeting($greeting)
            ->line($line1)
            ->line('> *' . Str::limit(strip_tags($this->reply->body), 150) . '*')
            ->action('View Full Reply', $url)
            ->line('Please review the ticket at your earliest convenience.');
    }

    /**
     * Get the array representation of the notification.
     * (For the database/in-app channel)
     */
    public function toArray(object $notifiable): array
    {
        $replier = $this->reply->user;
        $url = '';

        if ($notifiable->isAgent()) {
            $url = route('agent.tickets.show', $this->ticket->id);
        } else if ($notifiable->isAdmin()) {
            $url = route('admin.tickets.show', $this->ticket->id);
        } else {
            $url = route('client.tickets.show', $this->ticket->id);
        }

        return [
            'ticket_id' => $this->ticket->id,
            'title' => $this->ticket->title,
            'message' => "New reply from {$replier->name} on ticket #{$this->ticket->id}.",
            'url' => $url,
            'icon' => 'fas fa-reply', // Example FontAwesome icon
        ];
    }
}
