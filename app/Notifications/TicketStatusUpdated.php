<?php

namespace App\Notifications;

use App\Models\Ticket;
use App\Models\User; // <-- Import User model
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;

class TicketStatusUpdated extends Notification implements ShouldQueue
{
    use Queueable;

    public Ticket $ticket;
    public string $oldStatus;
    public string $newStatus;
    public User $updater;

    /**
     * Create a new notification instance.
     *
     * @param \App\Models\Ticket $ticket
     * @param string $oldStatus The name of the old status
     * @param string $newStatus The name of the new status
     * @param \App\Models\User $updater The user who made the change
     */
    public function __construct(Ticket $ticket, string $oldStatus, string $newStatus, User $updater)
    {
        $this->ticket = $ticket;
        $this->oldStatus = $oldStatus;
        $this->newStatus = $newStatus;
        $this->updater = $updater;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        // $notifiable here will be either the client or an agent (User model)
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $isAgent = $notifiable->isAgent();
        // Generate the correct URL based on the recipient's role
        $url = $isAgent
            ? route('agent.tickets.show', $this->ticket)
            : route('client.tickets.show', $this->ticket);

        $greeting = 'Hello ' . $notifiable->name . ',';
        $subject = "Update on Ticket: #" . $this->ticket->id;
        $line1 = "There's an update on a support ticket related to you:";

        // Customize line based on recipient
        if ($isAgent) {
            $line1 = "There's an update on a ticket assigned to you:";
        } else { // For the client
            $line1 = "There's an update on your support ticket:";
        }

        return (new MailMessage)
            ->subject($subject)
            ->greeting($greeting)
            ->line($line1)
            ->line('**' . Str::limit($this->ticket->title, 50) . '**')
            ->line("The status has been changed from **{$this->oldStatus}** to **{$this->newStatus}** by {$this->updater->name}.")
            ->action('View Ticket Details', $url)
            ->line('Thank you for your attention to this matter.');
    }

    /**
     * Get the array representation of the notification.
     * (For the database/in-app channel)
     */
    public function toArray(object $notifiable): array
    {
        $isAgent = $notifiable->isAgent();
        // Generate the correct URL for the in-app notification
        $url = $isAgent
            ? route('agent.tickets.show', $this->ticket->id)
            : route('client.tickets.show', $this->ticket->id);

        return [
            'ticket_id' => $this->ticket->id,
            'title' => $this->ticket->title,
            'message' => "Status of ticket #{$this->ticket->id} changed to '{$this->newStatus}' by {$this->updater->name}.",
            'url' => $url,
            'icon' => 'fas fa-info-circle', // Example FontAwesome icon
        ];
    }
}
