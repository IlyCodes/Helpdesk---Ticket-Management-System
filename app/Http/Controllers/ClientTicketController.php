<?php

namespace App\Http\Controllers;

use App\Events\TicketCreated;
use App\Models\Ticket;
use App\Models\TicketCategory;
use App\Models\TicketStatus;
use App\Http\Requests\StoreTicketRequest;
use App\Http\Requests\StoreTicketReplyRequest;
use App\Notifications\TicketReopened;
use App\Notifications\TicketReplied;
use App\Notifications\TicketStatusUpdated;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage; // For file uploads
use App\Notifications\TicketSubmitted; // For notification
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class ClientTicketController extends Controller
{
    public function __construct()
    {
        // Ensure only clients can access these methods, or apply middleware in routes
        $this->middleware('auth');
        $this->middleware('role:client'); // Assuming you have role middleware
    }

    /**
     * Display a listing of the client's tickets.
     */
    public function index()
    {
        $user = Auth::user();
        $tickets = Ticket::with(['category', 'status'])
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('client.tickets.index', compact('tickets'));
    }

    /**
     * Show the form for creating a new ticket.
     */
    public function create()
    {
        $categories = TicketCategory::orderBy('name')->get();
        return view('client.tickets.create', compact('categories'));
    }

    /**
     * Store a newly created ticket in storage.
     */
    public function store(StoreTicketRequest $request)
    {
        $user = Auth::user();
        $statusNotAssigned = TicketStatus::where('slug', 'not-assigned')->firstOrFail();

        $ticket = Ticket::create([
            'user_id' => $user->id,
            'title' => $request->input('title'),
            'description' => $request->input('description'),
            'category_id' => $request->input('category_id'),
            'status_id' => $statusNotAssigned->id,
            'priority' => $request->input('priority', 'medium'),
        ]);

        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                // Store file in 'ticket_attachments/ticket_id/filename'
                $filePath = $file->store("ticket_attachments/{$ticket->id}", 'public');
                $ticket->attachments()->create([
                    'file_path' => $filePath,
                    'original_name' => $file->getClientOriginalName(),
                    'mime_type' => $file->getClientMimeType(),
                    'size' => $file->getSize(),
                ]);
            }
        }

        // Send notification to client (and maybe admins/relevant department)
        try {
            TicketCreated::dispatch($ticket);
            $user->notify(new TicketSubmitted($ticket));
        } catch (\Exception $e) {
            // Log email sending failure
            \Log::error('Failed to send ticket submission email: ' . $e->getMessage());
        }


        return redirect()->route('client.tickets.show', $ticket)
            ->with('success', 'Ticket submitted successfully! Your ticket ID is #' . $ticket->id);
    }

    /**
     * Display the specified ticket.
     */
    public function show(Ticket $ticket)
    {
        // Authorize: Ensure the logged-in client owns this ticket
        if (Auth::id() !== $ticket->user_id) {
            abort(403, 'Unauthorized action.');
        }

        $ticket->load(['user', 'category', 'status', 'attachments', 'replies.user']);
        return view('client.tickets.show', compact('ticket'));
    }

    /**
     * Store a new reply for the specified ticket.
     */
    public function storeReply(StoreTicketReplyRequest $request, Ticket $ticket)
    {
        // Authorization is handled by StoreTicketReplyRequest

        $reply = $ticket->replies()->create([
            'user_id' => Auth::id(),
            'body' => $request->input('body'),
            'is_internal' => false, // Clients cannot make internal notes
        ]);

        $ticket->touch();

        // Notify assigned agent (if any and not the one replying) or admins
        if ($ticket->agent && $ticket->agent_id !== Auth::id()) {
            $ticket->agent->notify(new TicketReplied($ticket, $reply));
        }

        return redirect()->route('client.tickets.show', $ticket)
            ->with('success', 'Your reply has been submitted.');
    }

    public function reopen(Request $request, Ticket $ticket)
    {
        // Authorization: Ensure the logged-in client owns this ticket
        if (Auth::id() !== $ticket->user_id) {
            abort(403, 'Unauthorized action.');
        }

        // Authorization: Check if ticket is in a reopenable state
        $reopenableStatuses = ['resolved', 'closed'];
        if (!in_array($ticket->status->slug, $reopenableStatuses)) {
            return redirect()->back()->with('error', 'This ticket cannot be reopened.');
        }

        // Authorization: Check if ticket is within the reopening window (e.g., 14 days)
        $reopenWindow = 14;
        if ($ticket->updated_at->lt(Carbon::now()->subDays($reopenWindow))) {
            return redirect()->back()->with('error', "Tickets can only be reopened within {$reopenWindow} days of being resolved or closed.");
        }

        // Validation: Ensure a reason is provided
        $request->validate([
            'reopen_comment' => 'required|string|min:10|max:5000',
        ]);

        // Update the Ticket
        // Find the 'Open' status
        $openStatus = TicketStatus::where('slug', 'open')->firstOrFail();

        $ticket->status_id = $openStatus->id;
        $ticket->save();

        // Add the client's comment as a new reply
        $reply = $ticket->replies()->create([
            'user_id' => Auth::id(),
            'body' => $request->input('reopen_comment'),
        ]);

        // Notify the assigned agent
        if ($ticket->agent) {
            try {
                $ticket->agent->notify(new TicketReopened($ticket, $reply));
            } catch (\Exception $e) {
                Log::error("Failed to send TicketReopened notification for ticket #{$ticket->id}: " . $e->getMessage());
            }
        }

        return redirect()->route('client.tickets.show', $ticket)
            ->with('success', 'Ticket has been successfully reopened and your comment was added.');
    }

    public function close(Ticket $ticket)
    {
        // Authorization: Ensure the logged-in client owns this ticket
        if (Auth::id() !== $ticket->user_id) {
            abort(403, 'Unauthorized action.');
        }

        // Condition: Only allow closing if the status is 'Resolved'
        if ($ticket->status->slug !== 'resolved') {
            return redirect()->route('client.tickets.show', $ticket)
                ->with('error', 'This ticket can only be closed if it is in a "Resolved" state.');
        }

        // Save old status name for notification
        $oldStatusName = $ticket->status->name;

        // Update the Ticket
        $closedStatus = TicketStatus::where('slug', 'closed')->firstOrFail();

        $ticket->status_id = $closedStatus->id;
        $ticket->save();

        $client = Auth::user();

        // Add a system-like comment for clarity
        $ticket->replies()->create([
            'user_id' => Auth::id(),
            'body' => 'Ticket confirmed as resolved and closed by client.',
            'is_internal' => true, // Keep this as an internal note for staff
        ]);

        // Notify the assigned agent that the client has closed the ticket
        if ($ticket->agent) {
            try {
                // Using TicketStatusUpdated, passing the client as the 'updater'
                $ticket->agent->notify(new TicketStatusUpdated($ticket, $oldStatusName, $closedStatus->name, $client));
            } catch (\Exception $e) {
                Log::error("Failed to send close confirmation notification to agent for ticket #{$ticket->id}: " . $e->getMessage());
            }
        }

        return redirect()->route('client.tickets.show', $ticket)
            ->with('success', 'Thank you for your confirmation! The ticket has been closed.');
    }
}
