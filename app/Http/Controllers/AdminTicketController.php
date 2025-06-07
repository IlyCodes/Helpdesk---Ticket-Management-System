<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\TicketStatus;
use App\Models\TicketCategory;
use App\Models\User; // For listing agents
use App\Models\TicketAssignmentLog;
use App\Http\Requests\StoreTicketReplyRequest; // Can be reused by admin
use App\Notifications\TicketAssignedToAgent;
use App\Notifications\TicketReplied;
use App\Notifications\TicketStatusUpdated;
use App\Services\TicketAssignmentService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
// use App\Notifications\TicketAssignedToAgent;
// use App\Notifications\TicketStatusUpdatedByAdmin;
// use App\Notifications\TicketRepliedByAdmin;


class AdminTicketController extends Controller
{
    protected TicketAssignmentService $ticketAssignmentService;

    public function __construct(TicketAssignmentService $ticketAssignmentService)
    {
        $this->middleware('auth');
        $this->middleware('role:admin'); // Ensure only admins can access
        $this->ticketAssignmentService = $ticketAssignmentService;
    }

    /**
     * Display the admin statistics dashboard.
     */
    public function dashboard()
    {
        // Key Metrics
        $totalTickets = Ticket::count();

        $openTickets = Ticket::whereHas('status', fn($q) => $q->whereNotIn('slug', ['resolved', 'closed']))->count();
        $unassignedTickets = Ticket::whereNull('assigned_agent_id')->count();
        $resolvedToday = Ticket::whereHas('status', fn($q) => $q->where('slug', 'resolved'))
            ->whereDate('updated_at', today())->count();

        // Data for "Tickets by Status" Chart
        $ticketsByStatus = Ticket::select('status_id', DB::raw('count(*) as count'))
            ->groupBy('status_id')
            ->with('status:id,name,slug') // Eager load status details
            ->get();

        $statusLabels = $ticketsByStatus->pluck('status.name');
        $statusData = $ticketsByStatus->pluck('count');

        // Define a color map for statuses to ensure consistent chart colors
        $statusColors = $ticketsByStatus->map(function ($item) {
            $colorMap = [
                'open' => 'rgba(54, 162, 235, 0.7)',
                'pending' => 'rgba(255, 206, 86, 0.7)',
                'in-progress' => 'rgba(75, 192, 192, 0.7)',
                'resolved' => 'rgba(40, 167, 69, 0.7)',
                'closed' => 'rgba(108, 117, 125, 0.7)',
                'not-assigned' => 'rgba(220, 53, 69, 0.7)',
            ];
            return $colorMap[$item->status->slug] ?? 'rgba(201, 203, 207, 0.7)';
        });

        // Data for "Tickets by Category" Chart
        $ticketsByCategory = Ticket::select('category_id', DB::raw('count(*) as count'))
            ->groupBy('category_id')
            ->with('category:id,name')
            ->get();

        $categoryLabels = $ticketsByCategory->pluck('category.name');
        $categoryData = $ticketsByCategory->pluck('count');

        return view('admin.dashboard', compact(
            'totalTickets',
            'openTickets',
            'unassignedTickets',
            'resolvedToday',
            'statusLabels',
            'statusData',
            'statusColors',
            'categoryLabels',
            'categoryData'
        ));
    }

    /**
     * Display a listing of all tickets with filters.
     */
    public function index(Request $request)
    {
        $query = Ticket::with(['user', 'category', 'status', 'agent']);

        // Search
        if ($request->filled('search')) {
            $searchTerm = $request->input('search');
            $query->where(function ($q) use ($searchTerm) {
                $q->where('tickets.id', 'like', "%{$searchTerm}%")
                    ->orWhere('tickets.title', 'like', "%{$searchTerm}%")
                    ->orWhereHas('user', function ($userQuery) use ($searchTerm) {
                        $userQuery->where('name', 'like', "%{$searchTerm}%")
                            ->orWhere('email', 'like', "%{$searchTerm}%");
                    });
            });
        }

        // Filter by status
        if ($request->filled('status_slug')) {
            $status = TicketStatus::where('slug', $request->status_slug)->first();
            if ($status) {
                $query->where('status_id', $status->id);
            }
        }

        // Filter by category
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        // Filter by priority
        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }

        // Filter by agent
        if ($request->filled('agent_id')) {
            if ($request->agent_id == 'unassigned') {
                $query->whereNull('assigned_agent_id');
            } else {
                $query->where('assigned_agent_id', $request->agent_id);
            }
        }

        // Date range filter
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $tickets = $query->orderBy('updated_at', 'desc')->paginate(15);

        $statuses = TicketStatus::orderBy('name')->get();
        $categories = TicketCategory::orderBy('name')->get();
        $agents = User::where('role', 'agent')->orderBy('name')->get();
        $priorities = ['low', 'medium', 'high'];


        return view('admin.tickets.index', compact('tickets', 'statuses', 'categories', 'agents', 'priorities'));
    }

    /**
     * Display the specified ticket for the admin.
     */
    public function show(Ticket $ticket)
    {
        $ticket->load(['user', 'category', 'status', 'attachments', 'replies.user', 'agent', 'assignmentLogs.admin', 'assignmentLogs.assignedAgent', 'assignmentLogs.previousAgent']);
        $statuses = TicketStatus::orderBy('name')->get();
        $agents = User::where('role', 'agent')->orderBy('name')->get();

        return view('admin.tickets.show', compact('ticket', 'statuses', 'agents'));
    }

    /**
     * Assign an agent to the specified ticket.
     */
    public function assignAgent(Request $request, Ticket $ticket)
    {
        $validator = Validator::make($request->all(), [
            'agent_id' => ['required', 'exists:users,id', function ($attribute, $value, $fail) {
                $user = User::find($value);
                if (!$user || !$user->isAgent()) {
                    $fail('The selected user is not a valid agent.');
                }
            }],
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $newAgentId = $request->input('agent_id');
        $previousAgentId = $ticket->assigned_agent_id;

        if ($previousAgentId == $newAgentId) {
            return redirect()->route('admin.tickets.show', $ticket)
                ->with('info', 'Ticket is already assigned to this agent.');
        }

        $ticket->assigned_agent_id = $newAgentId;

        $notAssignedStatus = TicketStatus::where('slug', 'not-assigned')->first();
        $openStatus = TicketStatus::where('slug', 'open')->first();

        if ($notAssignedStatus && $ticket->status_id === $notAssignedStatus->id && $openStatus) {
            $ticket->status_id = $openStatus->id;
        }

        $ticket->save();

        TicketAssignmentLog::create([
            'ticket_id' => $ticket->id,
            'admin_id' => Auth::id(),
            'assigned_agent_id' => $newAgentId,
            'previous_agent_id' => $previousAgentId,
            'assigned_at' => now(),
        ]);

        $assignedAgent = User::find($newAgentId);
        if ($assignedAgent) {
            try {
                $assignedAgent->notify(new TicketAssignedToAgent($ticket));
            } catch (\Exception $e) {
                Log::error('Failed to send TicketAssignedToAgent notification: ' . $e->getMessage());
            }
        }

        return redirect()->route('admin.tickets.show', $ticket)
            ->with('success', 'Ticket assigned successfully to ' . $assignedAgent->name . '.');
    }

    /**
     * Update the status of the specified ticket by Admin.
     */
    public function updateStatus(Request $request, Ticket $ticket)
    {
        $validator = Validator::make($request->all(), [
            'status_id' => 'required|exists:ticket_statuses,id',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $oldStatus = $ticket->status->name;
        $ticket->status_id = $request->input('status_id');
        $ticket->save();
        $ticket->load('status');
        $newStatus = $ticket->status->name;
        $updater = Auth::user(); // Get the admin who is updating

        if ($oldStatus !== $newStatus) {
            $ticket->replies()->create([
                'user_id' => $updater->id,
                'body' => "Ticket status changed by admin from \"{$oldStatus}\" to \"{$newStatus}\".",
                'is_internal' => true,
            ]);

            // Notify client
            if ($ticket->user) {
                try {
                    $ticket->user->notify(new TicketStatusUpdated($ticket, $oldStatus, $newStatus, $updater));
                } catch (\Exception $e) {
                    Log::error("Failed to send status update notification to client for ticket #{$ticket->id}: " . $e->getMessage());
                }
            }
            // Notify assigned agent (if they are not the one making the change)
            if ($ticket->agent && $ticket->agent->id !== $updater->id) {
                try {
                    $ticket->agent->notify(new TicketStatusUpdated($ticket, $oldStatus, $newStatus, $updater));
                } catch (\Exception $e) {
                    Log::error("Failed to send status update notification to agent for ticket #{$ticket->id}: " . $e->getMessage());
                }
            }
        }

        return redirect()->route('admin.tickets.show', $ticket)
            ->with('success', 'Ticket status updated successfully.');
    }

    /**
     * Store a new reply for the specified ticket by the Admin.
     */
    public function storeReply(StoreTicketReplyRequest $request, Ticket $ticket)
    {
        $admin = Auth::user();

        $reply = $ticket->replies()->create([
            'user_id' => $admin->id,
            'body' => $request->input('body'),
            'is_internal' => $request->boolean('is_internal', false),
        ]);

        $ticket->touch();

        // If the reply is not an internal note, notify relevant users
        if (!$reply->is_internal) {
            // Notify the client who created the ticket
            if ($ticket->user) {
                try {
                    $ticket->user->notify(new TicketReplied($ticket, $reply));
                } catch (\Exception $e) {
                    Log::error("Failed to send reply notification to client for ticket #{$ticket->id}: " . $e->getMessage());
                }
            }

            // Notify the assigned agent, but only if they are not the one replying
            if ($ticket->agent && $ticket->agent->id !== $admin->id) {
                try {
                    $ticket->agent->notify(new TicketReplied($ticket, $reply));
                } catch (\Exception $e) {
                    Log::error("Failed to send reply notification to agent for ticket #{$ticket->id}: " . $e->getMessage());
                }
            }
        }

        return redirect()->route('admin.tickets.show', $ticket)
            ->with('success', 'Your reply has been submitted.');
    }

    /**
     * Trigger AI-powered assignment for a specific ticket.
     */
    public function assignWithAI(Ticket $ticket)
    {
        Log::info("Admin triggered AI assignment for Ticket #{$ticket->id}");

        // You might want to add a check here if the ticket is already in a 'resolved' or 'closed' state.
        $notAssignedStatus = TicketStatus::where('slug', 'not-assigned')->first();
        $initialStatusId = $ticket->status_id; // Save initial status

        // If the ticket is not in 'not-assigned' status, AI might still suggest.
        // Or, force it to be treated as if it needs fresh assignment.
        // For simplicity, let's allow AI to try assigning/re-assigning.

        $assignmentSuccessful = $this->ticketAssignmentService->assignTicketViaAI($ticket, Auth::id()); // Pass admin ID as assigner

        if ($assignmentSuccessful) {
            $newStatus = $ticket->fresh()->status->name; // Get the potentially updated status name
            $message = "AI assignment process initiated for Ticket #{$ticket->id}.";
            if ($ticket->agent) {
                $message .= " Ticket assigned to {$ticket->agent->name}. Status changed to '{$newStatus}'.";
            } else {
                $message .= " AI could not determine a suitable agent or an error occurred. Status might be '{$newStatus}'.";
            }
            return redirect()->route('admin.tickets.show', $ticket)->with('success', $message);
        } else {
            $currentStatusName = $ticket->status_id == $initialStatusId ? $ticket->status->name : TicketStatus::find($initialStatusId)->name;
            return redirect()->route('admin.tickets.show', $ticket)->with('error', "AI could not assign Ticket #{$ticket->id}. Please assign manually. Current status: '{$currentStatusName}'.");
        }
    }

    /**
     * Download a ticket as a PDF.
     */
    public function downloadPDF(Ticket $ticket)
    {
        // Eager load all necessary relationships for the PDF view
        $ticket->load(['user', 'agent', 'category', 'status', 'replies.user']);

        $data = ['ticket' => $ticket];

        // Generate the PDF
        $pdf = Pdf::loadView('admin.tickets.pdf', $data);

        // Set a nice filename for the download
        $fileName = 'ticket-' . $ticket->id . '-' . \Illuminate\Support\Str::slug($ticket->title) . '.pdf';

        // Stream the PDF to the browser for download
        return $pdf->stream($fileName);
    }
}
