<?php

namespace App\Http\Controllers;

use App\Models\Faq;
use Illuminate\Http\Request;

class WelcomeController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        // Fetch all active FAQs, ordered by the sort_order and then question
        $faqs = Faq::where('is_active', true)
            ->orderBy('sort_order', 'asc')
            ->orderBy('question', 'asc')
            ->get();

        return view('welcome', [
            'faqs' => $faqs
        ]);
    }
}
