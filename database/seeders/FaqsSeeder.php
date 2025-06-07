<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Faq;
use Illuminate\Support\Facades\DB;

class FaqsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Truncate the table first to avoid duplicate entries on re-seed
        DB::table('faqs')->truncate();

        $faqs = [
            [
                'question' => 'How do I reset my password?',
                'answer' => 'You can reset your password by clicking the "Forgot your password?" link on the login page. You will receive an email with instructions to create a new password. If you do not receive the email, please check your spam folder.',
                'keywords' => json_encode(['password', 'reset', 'forgot', 'login', 'account']),
                'is_active' => true,
                'sort_order' => 10,
            ],
            [
                'question' => 'How do I create a new support ticket?',
                'answer' => 'To create a new support ticket, please log in to your account. From your dashboard, click the "New Ticket" button. Fill out the form with a clear title, a detailed description of your issue, and select the most appropriate category. You can also attach files like screenshots to help us understand the problem better.',
                'keywords' => json_encode(['ticket', 'create', 'new', 'submit', 'request']),
                'is_active' => true,
                'sort_order' => 20,
            ],
            [
                'question' => 'What are the different ticket statuses?',
                'answer' => "Here is a quick guide to what each status means:\n- **Not Assigned:** Your ticket is in the queue waiting for an agent.\n- **Open:** Your ticket has been assigned to an agent.\n- **In Progress:** An agent is actively working on your ticket.\n- **Pending:** The agent is waiting for a response from you.\n- **Resolved:** The agent believes the issue is fixed. You can reopen it or confirm the fix.\n- **Closed:** The case is complete.",
                'keywords' => json_encode(['status', 'meaning', 'open', 'closed', 'resolved', 'pending']),
                'is_active' => true,
                'sort_order' => 30,
            ],
            [
                'question' => 'How can I check the status of my ticket?',
                'answer' => 'Log in to your account and go to the "My Tickets" section. You will see a list of all your submitted tickets along with their current status. Click on any ticket to view the full conversation history.',
                'keywords' => json_encode(['status', 'check', 'progress', 'update']),
                'is_active' => true,
                'sort_order' => 40,
            ],
            [
                'question' => 'My issue is resolved, what should I do?',
                'answer' => 'If an agent has set your ticket to "Resolved" and you agree the issue is fixed, you can either do nothing (it will close automatically after a few days) or you can click the "Close Ticket Now" link in the notification email to close it immediately. We appreciate your confirmation!',
                'keywords' => json_encode(['resolved', 'confirm', 'fixed', 'close']),
                'is_active' => true,
                'sort_order' => 50,
            ],
            [
                'question' => 'The problem came back after my ticket was resolved. What should I do?',
                'answer' => 'If your ticket was resolved or closed recently (within the last 14 days), you can navigate to the ticket page and use the "Reopen Ticket" form to add a comment. This will notify the original agent. If it has been longer than 14 days, please create a new ticket and reference the old ticket ID if possible.',
                'keywords' => json_encode(['reopen', 'problem again', 'not fixed', 'issue back']),
                'is_active' => true,
                'sort_order' => 60,
            ],
        ];

        // Insert the data into the faqs table
        Faq::insert($faqs);
    }
}
