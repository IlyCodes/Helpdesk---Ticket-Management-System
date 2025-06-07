<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Faq;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator; // For more complex validation if needed
use Illuminate\Validation\Rule as ValidationRule;

class AdminFaqController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:admin']);
    }

    /**
     * Display a listing of the FAQs.
     */
    public function index(Request $request)
    {
        $query = Faq::query();

        if ($request->filled('search')) {
            $searchTerm = $request->input('search');
            $query->where(function ($q) use ($searchTerm) {
                $q->where('question', 'like', "%{$searchTerm}%")
                    ->orWhere('answer', 'like', "%{$searchTerm}%")
                    ->orWhereJsonContains('keywords', $searchTerm); // Search in JSON keywords
            });
        }

        if ($request->filled('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        $faqs = $query->orderBy('sort_order', 'asc')->orderBy('question', 'asc')->paginate(15);
        return view('admin.faqs.index', compact('faqs'));
    }

    /**
     * Show the form for creating a new FAQ.
     */
    public function create()
    {
        return view('admin.faqs.create');
    }

    /**
     * Store a newly created FAQ in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'question' => 'required|string|max:1000|unique:faqs,question',
            'answer' => 'required|string',
            'keywords_text' => 'nullable|string|max:255', // For comma-separated keywords input
            'is_active' => 'sometimes|boolean',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $keywordsArray = [];
        if ($request->filled('keywords_text')) {
            $keywordsArray = array_map('trim', explode(',', $request->input('keywords_text')));
            $keywordsArray = array_filter($keywordsArray); // Remove empty elements
        }

        Faq::create([
            'question' => $request->input('question'),
            'answer' => $request->input('answer'),
            'keywords' => $keywordsArray,
            'is_active' => $request->boolean('is_active', false), // Default to false if not present
            'sort_order' => $request->input('sort_order', 0),
        ]);

        return redirect()->route('admin.faqs.index')->with('success', 'FAQ created successfully.');
    }

    /**
     * Display the specified FAQ. (Optional - edit is usually sufficient)
     */
    public function show(Faq $faq)
    {
        return view('admin.faqs.show', compact('faq')); // You'd need to create this view
    }

    /**
     * Show the form for editing the specified FAQ.
     */
    public function edit(Faq $faq)
    {
        // Convert keywords array back to comma-separated string for the form
        $keywordsText = is_array($faq->keywords) ? implode(', ', $faq->keywords) : '';
        return view('admin.faqs.edit', compact('faq', 'keywordsText'));
    }

    /**
     * Update the specified FAQ in storage.
     */
    public function update(Request $request, Faq $faq)
    {
        $request->validate([
            'question' => ['required', 'string', 'max:1000', ValidationRule::unique('faqs')->ignore($faq->id)],
            'answer' => 'required|string',
            'keywords_text' => 'nullable|string|max:255',
            'is_active' => 'sometimes|boolean',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $keywordsArray = [];
        if ($request->filled('keywords_text')) {
            $keywordsArray = array_map('trim', explode(',', $request->input('keywords_text')));
            $keywordsArray = array_filter($keywordsArray);
        }

        $faq->update([
            'question' => $request->input('question'),
            'answer' => $request->input('answer'),
            'keywords' => $keywordsArray,
            'is_active' => $request->boolean('is_active'),
            'sort_order' => $request->input('sort_order', 0),
        ]);

        return redirect()->route('admin.faqs.index')->with('success', 'FAQ updated successfully.');
    }

    /**
     * Remove the specified FAQ from storage.
     */
    public function destroy(Faq $faq)
    {
        $faq->delete();
        return redirect()->route('admin.faqs.index')->with('success', 'FAQ deleted successfully.');
    }
}
