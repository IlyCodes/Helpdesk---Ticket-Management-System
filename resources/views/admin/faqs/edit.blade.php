@extends('layouts.app')

@section('title', 'Edit FAQ: ' . Str::limit($faq->question, 30))

@section('content')
<div class="max-w-3xl mx-auto bg-white p-6 sm:p-8 rounded-lg shadow-md">
    <h2 class="text-2xl font-semibold text-gray-800 mb-6">Edit FAQ</h2>

    <form action="{{ route('admin.faqs.update', $faq) }}" method="POST" class="space-y-6">
        @csrf
        @method('PUT')

        <div>
            <label for="question" class="block text-sm font-medium text-gray-700">Question <span class="text-red-500">*</span></label>
            <textarea name="question" id="question" rows="3" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">{{ old('question', $faq->question) }}</textarea>
            @error('question') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="answer" class="block text-sm font-medium text-gray-700">Answer <span class="text-red-500">*</span></label>
            <textarea name="answer" id="answer" rows="6" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">{{ old('answer', $faq->answer) }}</textarea>
            @error('answer') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="keywords_text" class="block text-sm font-medium text-gray-700">Keywords (comma-separated)</label>
            <input type="text" name="keywords_text" id="keywords_text" value="{{ old('keywords_text', $keywordsText) }}"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                placeholder="e.g. password, reset, login issue">
            <p class="text-xs text-gray-500 mt-1">These help the AI find relevant FAQs.</p>
            @error('keywords_text') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
            <div>
                <label for="sort_order" class="block text-sm font-medium text-gray-700">Sort Order</label>
                <input type="number" name="sort_order" id="sort_order" value="{{ old('sort_order', $faq->sort_order) }}" min="0"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                @error('sort_order') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
            <div class="pt-5">
                <label for="is_active" class="inline-flex items-center">
                    <input type="hidden" name="is_active" value="0"> {{-- Send 0 if checkbox is not checked --}}
                    <input type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', $faq->is_active) ? 'checked' : '' }}
                        class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 h-4 w-4">
                    <span class="ml-2 text-sm text-gray-700">Active (visible to chatbot/users)</span>
                </label>
                @error('is_active') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
        </div>

        <div class="flex items-center justify-end pt-3 space-x-3">
            <a href="{{ route('admin.faqs.index') }}" class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50">Cancel</a>
            <button type="submit"
                class="inline-flex items-center justify-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-800 focus:outline-none focus:border-indigo-900 focus:ring ring-indigo-300">
                Update FAQ
            </button>
        </div>
    </form>
</div>
@endsection