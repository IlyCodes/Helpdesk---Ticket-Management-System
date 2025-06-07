<?php

namespace App\Notifications;

use App\Models\Ticket;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;

class TicketResolvedConfirmation extends Notification implements ShouldQueue
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
     */
    public function via(object $notifiable): array
    {
        return ['mail']; // We only need to send an email for this confirmation
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $viewUrl = route('client.tickets.show', $this->ticket);
        $closeUrl = route('client.tickets.close', $this->ticket);

        return (new MailMessage)
            ->subject('Your Support Ticket Has Been Resolved: #' . $this->ticket->id)
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line('Good news! Your support ticket has been marked as resolved by our team:')
            ->line('**' . Str::limit($this->ticket->title, 50) . '**')
            ->line('Please review the solution. If you are satisfied, you can close the ticket now.')
            ->line('If the issue is not resolved, you can reopen the ticket by replying to it on the ticket page.')
            ->action('View Ticket Details', $viewUrl)
            ->line('Or, if the issue is solved:')
            ->action('Close Ticket Now', $closeUrl);
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray(object $notifiable): array
    {
        return [
            'ticket_id' => $this->ticket->id,
            'title' => $this->ticket->title,
            'message' => "Your ticket #{$this->ticket->id} has been resolved. Please confirm the solution.",
            'url' => route('client.tickets.show', $this->ticket->id),
        ];
    }
}
