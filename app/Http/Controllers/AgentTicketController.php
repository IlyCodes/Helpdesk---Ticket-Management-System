<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\TicketStatus;
use App\Http\Requests\StoreTicketReplyRequest; // Re-use for replies
use App\Notifications\TicketReplied;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Notifications\TicketStatusUpdated; // You'll need to create this notification
use App\Notifications\TicketRepliedByAgent; // You'll need to create this notification
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class AgentTicketController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:agent'); // Ensure only agents can access
    }

    /**
     * Display a listing of tickets assigned to the logged-in agent.
     */
    public function index(Request $request)
    {
        $agent = Auth::user();
        $query = Ticket::with(['user', 'category', 'status'])
            ->where('assigned_agent_id', $agent->id);

        // Filter by status
        if ($request->has('status') && $request->status != '') {
            $status = TicketStatus::where('slug', $request->status)->first();
            if ($status) {
                $query->where('status_id', $status->id);
            }
        }

        $tickets = $query->orderBy('updated_at', 'desc')->paginate(10);
        $statuses = TicketStatus::orderBy('name')->get(); // For filter dropdown

        return view('agent.tickets.index', compact('tickets', 'statuses'));
    }

    /**
     * Display the specified ticket for the agent.
     */
    public function show(Ticket $ticket)
    {
        // Authorize: Ensure the ticket is assigned to the logged-in agent
        // Or allow if an admin is accessing (though admin would use AdminTicketController)
        if ($ticket->assigned_agent_id !== Auth::id()) {
            // If you want agents to only see their *own* tickets through this controller
            abort(403, 'This ticket is not assigned to you.');
        }

        $ticket->load(['user', 'category', 'status', 'attachments', 'replies.user', 'agent']);
        $statuses = TicketStatus::WhereIn('slug', ['in-progress', 'pending', 'resolved'])->orderBy('name')->get(); // For changing status

        return view('agent.tickets.show', compact('ticket', 'statuses'));
    }

    /**
     * Update the status of the specified ticket.
     */
    public function updateStatus(Request $request, Ticket $ticket)
    {
        // Authorization: Check if agent is assigned to the ticket
        if ($ticket->assigned_agent_id !== Auth::id()) {
            abort(403, 'This ticket is not assigned to you.');
        }

        // Authorization: Check if the ticket is already closed
        if ($ticket->status->slug === 'closed') {
            return redirect()->back()->with('error', 'A closed ticket cannot be modified.');
        }

        // Define the statuses an agent is allowed to set.
        $allowedSlugs = ['in-progress', 'pending', 'resolved'];
        $allowedStatusIds = TicketStatus::whereIn('slug', $allowedSlugs)->pluck('id')->toArray();

        $validator = Validator::make($request->all(), [
            'status_id' => [
                'required',
                'exists:ticket_statuses,id',
                Rule::in($allowedStatusIds),
            ],
        ]);

        $validator = Validator::make($request->all(), [
            'status_id' => 'required|exists:ticket_statuses,id',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $oldStatus = $ticket->status->name;
        $newStatusId = $request->input('status_id');

        if ($ticket->status_id != $newStatusId) {
            $ticket->status_id = $newStatusId;
            $ticket->save();
            $ticket->load('status');
            $newStatus = $ticket->status->name;
            $updater = Auth::user();

            $ticket->replies()->create([
                'user_id' => $updater->id,
                'body' => "Ticket status changed by agent from \"{$oldStatus}\" to \"{$newStatus}\".",
                'is_internal' => true,
            ]);

            // Notify client about status update
            if ($ticket->user) {
                try {
                    $ticket->user->notify(new TicketStatusUpdated($ticket, $oldStatus, $newStatus, $updater));
                } catch (\Exception $e) {
                    Log::error("Failed to send status update notification for ticket #{$ticket->id}: " . $e->getMessage());
                }
            }
            Log::info("Notification for status update on ticket #{$ticket->id} would be sent here.");
        }

        return redirect()->route('agent.tickets.show', $ticket)
            ->with('success', 'Ticket status updated successfully.');
    }

    /**
     * Store a new reply for the specified ticket by the agent.
     */
    public function storeReply(StoreTicketReplyRequest $request, Ticket $ticket)
    {
        $agent = Auth::user();

        // Additional authorization check to ensure agent is assigned
        if ($ticket->assigned_agent_id !== $agent->id && !$agent->isAdmin()) {
            abort(403, 'You can only reply to tickets assigned to you.');
        }

        $reply = $ticket->replies()->create([
            'user_id' => $agent->id,
            'body' => $request->input('body'),
            'is_internal' => $request->boolean('is_internal', false),
        ]);

        // Notify client about the new reply (if it's not an internal note)
        if (!$reply->is_internal && $ticket->user) {
            try {
                $ticket->user->notify(new TicketReplied($ticket, $reply));
            } catch (\Exception $e) {
                Log::error("Failed to send TicketReplied notification for ticket #{$ticket->id}: " . $e->getMessage());
            }
        }

        return redirect()->route('agent.tickets.show', $ticket)
            ->with('success', 'Your reply has been submitted successfully.');
    }
}
