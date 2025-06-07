<?php

namespace App\Http\Controllers;

use App\Models\Faq; // <-- Import the Faq model
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth; 
use Illuminate\Support\Str; // <-- Import Str for keyword processing

class ChatbotController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth'); 
    }

    /**
     * Handle an incoming chat message from the user.
     */
    public function sendMessage(Request $request)
    {
        $request->validate([
            'message' => 'required|string|max:2000',
            'history' => 'nullable|array', 
        ]);

        $userMessage = $request->input('message');
        $chatHistory = $request->input('history', []);

        // --- 1. Attempt to find a relevant FAQ ---
        $faqReply = $this->findRelevantFaq($userMessage);

        if ($faqReply) {
            Log::info("Chatbot: Found relevant FAQ for message: '{$userMessage}'. Replying with FAQ answer.");
            return response()->json(['reply' => $faqReply]);
        }

        Log::info("Chatbot: No direct FAQ match for '{$userMessage}'. Proceeding to Gemini API.");

        // --- 2. If no FAQ found, proceed to Gemini API ---
        $apiKey = config('services.gemini.api_key');
        $geminiModel = config('services.gemini.model', 'gemini-1.5-flash-latest');
        // Corrected default base URI to be a valid URL string
        $baseUri = config('services.gemini.base_uri', '[https://generativelanguage.googleapis.com](https://generativelanguage.googleapis.com)');
        $endpoint = rtrim($baseUri, '/') . "/v1beta/models/{$geminiModel}:generateContent?key={$apiKey}";


        if (!$apiKey) {
            Log::error('Gemini API Key is not configured.');
            return response()->json(['reply' => 'Sorry, the AI chatbot is currently unavailable. API key missing.'], 500);
        }

        $systemInstruction = "You are 'SupportSpark', a friendly and helpful AI assistant for our Help Desk application. Your goal is to assist users with their support queries. You can answer general questions, help them understand how to use the help desk, guide them in creating effective support tickets, or provide information based on our FAQs if possible. If you cannot help, politely suggest creating a support ticket. Keep your responses concise and clear.";
        
        $contents = [];
        // Add existing chat history (simplified - consider more sophisticated history management)
        foreach ($chatHistory as $entry) {
            if (isset($entry['role']) && isset($entry['parts']) && !empty($entry['parts'][0]['text'])) {
                 $contents[] = [
                    'role' => $entry['role'], 
                    'parts' => [['text' => $entry['parts'][0]['text']]],
                ];
            }
        }
        
        $currentUserMessageContent = $systemInstruction;
        if (!empty($chatHistory) && isset(end($chatHistory)['parts'][0]['text'])) {
            $currentUserMessageContent .= "\n\nPrevious conversation turn (if any, for context, was about their query similar to): '" . Str::limit(end($chatHistory)['parts'][0]['text'], 100) . "'.";
        }
        $currentUserMessageContent .= "\n\nUser's current message: " . $userMessage;

        $contents[] = [
            'role' => 'user',
            'parts' => [['text' => $currentUserMessageContent]],
        ];
        
        $data = [
            'contents' => $contents,
            'generationConfig' => [
                'temperature' => 0.7,
                'maxOutputTokens' => 500, // Adjust as needed
                'topP' => 0.95,
                'topK' => 40
            ]
        ];

        Log::info('Sending to Gemini: ', ['endpoint' => $endpoint, 'model' => $geminiModel]);
        Log::debug('Gemini Payload: ', $data);

        try {
            $response = Http::timeout(30)->post($endpoint, $data);

            if ($response->successful()) {
                $responseData = $response->json();
                Log::info('Gemini Response: ', $responseData);

                $botReply = data_get($responseData, 'candidates.0.content.parts.0.text', 'Sorry, I could not process that. Please try again.');
                
                if (isset($responseData['candidates'][0]['finishReason']) && $responseData['candidates'][0]['finishReason'] === 'SAFETY') {
                    $botReply = "I'm unable to respond to that due to safety guidelines.";
                }
                return response()->json(['reply' => $botReply]);
            } else {
                Log::error('Gemini API Error: ' . $response->status(), (array) $response->json());
                $errorMessage = 'Sorry, there was an error communicating with the AI. Please try again later.';
                return response()->json(['reply' => $errorMessage], $response->status()); // Return actual status
            }
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            Log::error('Gemini API Connection Exception: ' . $e->getMessage());
            return response()->json(['reply' => 'Sorry, I could not connect to the AI service. Please try again later.'], 503);
        } catch (\Exception $e) {
            Log::error('General Chatbot Exception: ' . $e->getMessage(), ['trace' => substr($e->getTraceAsString(), 0, 500)]);
            return response()->json(['reply' => 'An unexpected error occurred with the chatbot.'], 500);
        }
    }

    /**
     * Find a relevant FAQ based on user message.
     * This is a basic implementation. Can be improved with more sophisticated NLP/search.
     */
    private function findRelevantFaq(string $userMessage): ?string
    {
        // Basic keyword extraction: lowercase, remove punctuation, split into words.
        // Consider using a stop word list or more advanced tokenization.
        $keywords = array_unique(str_word_count(strtolower(preg_replace('/[^\w\s]/', '', $userMessage)), 1));
        
        if (empty($keywords)) {
            return null;
        }

        // Search for FAQs
        // Prioritize direct keyword matches in the 'keywords' JSON field first,
        // then broader matches in question/answer.
        $faqs = Faq::where('is_active', true)
            ->where(function ($query) use ($keywords) {
                foreach ($keywords as $keyword) {
                    if (Str::length($keyword) > 2) { // Avoid very short/common words if not using stop list
                        $query->orWhereJsonContains('keywords', $keyword);
                    }
                }
            })
            ->orderBy('sort_order', 'asc')
            ->limit(5) // Limit initial candidates from keyword field
            ->get();

        if ($faqs->isEmpty()) {
             // If no direct keyword match, try a broader search in question
            $faqs = Faq::where('is_active', true)
                ->where(function ($query) use ($keywords) {
                    foreach ($keywords as $keyword) {
                        if (Str::length($keyword) > 2) {
                             $query->orWhere('question', 'LIKE', "%{$keyword}%");
                        }
                    }
                })
                ->orderBy('sort_order', 'asc')
                ->limit(3) // Limit candidates from question search
                ->get();
        }


        // For simplicity, return the first match's answer.
        // You could implement a scoring system for multiple matches.
        if ($faqs->isNotEmpty()) {
            $bestMatch = $faqs->first(); // Or implement more sophisticated selection
            Log::info("FAQ match found: ID {$bestMatch->id} for user message '{$userMessage}' based on keywords: " . implode(', ', $keywords));
            return $bestMatch->answer;
        }

        return null;
    }
}