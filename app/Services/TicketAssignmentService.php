<?php

namespace App\Services;

use App\Models\Ticket;
use App\Models\User;
use App\Models\TicketStatus;
use App\Models\TicketAssignmentLog;
use App\Notifications\TicketAssignedToAgent;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TicketAssignmentService
{
    /**
     * Attempt to assign a ticket using AI.
     *
     * @param Ticket $ticket The ticket to assign.
     * @param int|null $assignerId The ID of the user/admin triggering the assignment (null for system/event-driven).
     * @return bool True if assignment was attempted and successful (or AI decided not to assign), false on error preventing assignment.
     */
    public function assignTicketViaAI(Ticket $ticket, ?int $assignerId = null): bool
    {
        $triggeredBy = $assignerId ?? 'System/Event';
        Log::info("AI Assignment Service started for Ticket #{$ticket->id}. Triggered by User ID: {$triggeredBy}.");

        // 1. Gather Agent Information
        $availableAgents = User::where('role', 'agent')->where('is_active', true)->get();

        if ($availableAgents->isEmpty()) {
            Log::warning("No active agents available for AI assignment of Ticket #{$ticket->id}.");
            return false; // Indicate failure to assign
        }

        $agentProfiles = "Available Support Agents:\n";
        foreach ($availableAgents as $agent) {
            $openLoad = $agent->assignedTickets()
                ->whereHas('status', fn($q) => $q->whereNotIn('slug', ['resolved', 'closed']))
                ->count();
            // Corrected: Evaluate specialization before string interpolation
            $specialization = $agent->specialization ?? 'General';
            $agentProfiles .= "- Agent ID: {$agent->id}, Name: {$agent->name}, Specialization: {$specialization}, Current Open Tickets: {$openLoad}\n";
        }

        // 2. Construct the Prompt for Gemini
        $ticketDetails = "Ticket Information:\nID: {$ticket->id}\nTitle: {$ticket->title}\nDescription: " . strip_tags($ticket->description) . "\nCategory: {$ticket->category->name}\nPriority: {$ticket->priority}\nSubmitted by: {$ticket->user->name}\n";
        $systemInstruction = "You are an AI assistant that assigns support tickets. Analyze the ticket and available agents.";
        
        $taskInstruction = "Your task is to recommend the most suitable agent ID based on the following rules, in order of priority:
        1.  **Analyze Content:** First, carefully read the ticket's **Title** and **Description** to understand the user's actual problem, regardless of the selected 'Category'.
        2.  **Match Specialization:** Find an agent whose **Specialization** best matches the problem you identified from the ticket's content. The selected 'Category' can be used as a secondary hint if the text is ambiguous.
        3.  **Tie-Breaker:** If multiple agents are a good match based on their specialization, choose the one with the lowest number of 'Current Open Tickets'.
        4.  **Fallback:** If no specialized agent is a good match for the issue, assign the ticket to a 'General Support' agent with the lowest number of 'Current Open Tickets'.
        5.  **No Match:** If no agent is suitable, respond with '0'.

        Your response MUST be only the single, numerical Agent ID and nothing else.";

        $prompt = $systemInstruction . "\n\n" . $ticketDetails . "\n\n" . $agentProfiles . "\n\n" . $taskInstruction;

        // 3. Gemini API Call
        $apiKey = config('services.gemini.api_key');
        $geminiModel = config('services.gemini.model_for_assignment', config('services.gemini.model', 'gemini-1.5-flash-latest'));
        $endpoint = rtrim(config('services.gemini.base_uri', '[https://generativelanguage.googleapis.com](https://generativelanguage.googleapis.com)'), '/') . "/v1beta/models/{$geminiModel}:generateContent?key={$apiKey}";

        $payload = [
            'contents' => [['role' => 'user', 'parts' => [['text' => $prompt]]]],
            'generationConfig' => [
                'temperature' => 0.2,
                'maxOutputTokens' => 10,
                'topP' => 0.95,
                'topK' => 40
            ]
        ];

        Log::info("Sending AI assignment request to Gemini for Ticket #{$ticket->id}. Model: {$geminiModel}");
        Log::debug("Gemini Payload for Ticket #{$ticket->id}: ", $payload);

        try {
            $response = Http::timeout(60)->post($endpoint, $payload);

            if ($response->successful()) {
                $responseData = $response->json();
                Log::info("Gemini AI assignment response for Ticket #{$ticket->id}: ", $responseData);

                $suggestedText = data_get($responseData, 'candidates.0.content.parts.0.text');

                if (isset($responseData['candidates'][0]['finishReason']) && $responseData['candidates'][0]['finishReason'] === 'SAFETY') {
                    Log::warning("Gemini assignment for Ticket #{$ticket->id} blocked by safety. AI Response: {$suggestedText}");
                    return false; // Indicate AI could not assign due to safety
                }
                if (empty($suggestedText)) {
                    Log::warning("Gemini response for Ticket #{$ticket->id} was empty or missing text part.");
                    return false; // Indicate AI could not provide a suggestion
                }

                $suggestedAgentId = filter_var(trim(preg_replace('/[^0-9]/', '', $suggestedText)), FILTER_VALIDATE_INT);

                if ($suggestedAgentId && $suggestedAgentId > 0) {
                    $agentToAssign = User::where('id', $suggestedAgentId)->where('role', 'agent')->where('is_active', true)->first();
                    if ($agentToAssign) {
                        $previousAgentId = $ticket->assigned_agent_id;
                        $ticket->assigned_agent_id = $agentToAssign->id;

                        $notAssignedStatus = TicketStatus::where('slug', 'not-assigned')->first();
                        $openStatus = TicketStatus::where('slug', 'open')->first();
                        // Change status only if it was 'not-assigned' or if it's a deliberate re-assignment logic
                        if ($openStatus && (!$previousAgentId || $ticket->status_id === $notAssignedStatus?->id)) {
                            $ticket->status_id = $openStatus->id;
                        }
                        $ticket->save();

                        TicketAssignmentLog::create([
                            'ticket_id' => $ticket->id,
                            'admin_id' => $assignerId, // Admin who triggered, or null if system
                            'assigned_agent_id' => $agentToAssign->id,
                            'previous_agent_id' => $previousAgentId,
                            'assigned_at' => now(),
                        ]);

                        Log::info("Ticket #{$ticket->id} AI-assigned to Agent #{$agentToAssign->id} ({$agentToAssign->name}).");
                        $agentToAssign->notify(new TicketAssignedToAgent($ticket));
                        return true; // Successfully assigned
                    } else {
                        Log::warning("Gemini suggested Agent ID '{$suggestedAgentId}' for Ticket #{$ticket->id}, but this agent is invalid or not active.");
                    }
                } else {
                    Log::info("Gemini did not suggest a valid agent ID (received '{$suggestedText}') for Ticket #{$ticket->id}. Leaving as is or for manual assignment.");
                }
                return false; // AI suggested 0 or invalid, so not "successfully assigned by AI"
            } else {
                Log::error("Gemini AI assignment API Error for Ticket #{$ticket->id}: " . $response->status(), (array) $response->json());
                return false; // API error
            }
        } catch (\Exception $e) {
            Log::error("Exception during AI ticket assignment for Ticket #{$ticket->id}: " . $e->getMessage(), ['trace' => substr($e->getTraceAsString(), 0, 1000)]);
            return false; // General error
        }
    }
}
