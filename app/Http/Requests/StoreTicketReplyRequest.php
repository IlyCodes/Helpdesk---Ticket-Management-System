<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use App\Models\Ticket;

class StoreTicketReplyRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        if (!Auth::check()) {
            return false;
        }

        $ticket = $this->route('ticket'); // Assuming route model binding for ticket

        if (!$ticket instanceof Ticket) {
             // If not using route model binding, load it manually
            $ticketId = $this->route('ticket_id') ?: $this->input('ticket_id');
            $ticket = Ticket::find($ticketId);
        }

        if (!$ticket) {
            return false; // Ticket not found
        }
        
        $user = Auth::user();

        // Client can reply to their own ticket
        if ($user->isClient() && $ticket->user_id === $user->id) {
            return true;
        }

        // Agent can reply to assigned ticket or any ticket if they have broader permissions (handled by role middleware)
        if ($user->isAgent() && ($ticket->assigned_agent_id === $user->id || $user->can('replyToAnyTicket'))) { // Define 'replyToAnyTicket' gate if needed
             return true;
        }
        
        // Admin can reply to any ticket
        if ($user->isAdmin()) {
            return true;
        }

        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'body' => 'required|string|min:5',
            'is_internal' => 'sometimes|boolean', // Only for agents/admins
        ];
    }
}
