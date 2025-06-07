@extends('layouts.app')

@section('title', 'Manage FAQs')

@section('content')
<div class="bg-white shadow-md overflow-hidden sm:rounded-lg">
    <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
        <div class="flex flex-col sm:flex-row justify-between items-center gap-4">
            <h2 class="text-xl lg:text-2xl font-semibold text-gray-800 leading-tight">Manage FAQs</h2>
            <a href="{{ route('admin.faqs.create') }}" class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-500 active:bg-green-700 focus:outline-none focus:border-green-700 focus:ring ring-green-300 disabled:opacity-25 transition ease-in-out duration-150">
                Add New FAQ
            </a>
        </div>
    </div>

    <div class="p-4 sm:p-6 bg-gray-50 border-b border-gray-200">
        <form method="GET" action="{{ route('admin.faqs.index') }}" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4 items-end">
            <div>
                <label for="search" class="block text-sm font-medium text-gray-700">Search Question/Answer/Keyword</label>
                <input type="text" name="search" id="search" value="{{ request('search') }}" placeholder="Search..."
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
            </div>
            <div>
                <label for="is_active" class="block text-sm font-medium text-gray-700">Status</label>
                <select name="is_active" id="is_active" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm py-2">
                    <option value="">All</option>
                    <option value="1" {{ request('is_active') === '1' ? 'selected' : '' }}>Active</option>
                    <option value="0" {{ request('is_active') === '0' ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>
            <div class="flex space-x-2">
                <button type="submit" class="w-full sm:w-auto inline-flex justify-center items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700">Filter</button>
                @if(request()->has('search') || request()->has('is_active'))
                <a href="{{ route('admin.faqs.index') }}" class="w-full sm:w-auto inline-flex justify-center items-center px-4 py-2 border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-50">Clear</a>
                @endif
            </div>
        </form>
    </div>

    @if($faqs->isEmpty())
    <div class="p-6 text-center text-gray-500">
        No FAQs found. <a href="{{ route('admin.faqs.create') }}" class="text-indigo-600 hover:underline">Add one now!</a>
    </div>
    @else
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Sort</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Question</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hidden md:table-cell">Keywords</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th scope="col" class="relative px-6 py-3"><span class="sr-only">Actions</span></th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @foreach ($faqs as $faq)
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $faq->sort_order }}</td>
                    <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ Str::limit($faq->question, 70) }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 hidden md:table-cell">
                        @if($faq->keywords && count($faq->keywords) > 0)
                        @foreach($faq->keywords as $keyword)
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800 mr-1 mb-1">{{ $keyword }}</span>
                        @endforeach
                        @else
                        -
                        @endif
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                        @if($faq->is_active)
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Active</span>
                        @else
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">Inactive</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-2">
                        <a href="{{ route('admin.faqs.edit', $faq) }}" class="text-indigo-600 hover:text-indigo-900">Edit</a>
                        <form action="{{ route('admin.faqs.destroy', $faq) }}" method="POST" class="inline-block" onsubmit="return confirm('Are you sure you want to delete this FAQ?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-600 hover:text-red-900">Delete</button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @if($faqs->hasPages())
    <div class="px-4 py-3 border-t border-gray-200 sm:px-6 bg-white">
        {{ $faqs->appends(request()->query())->links() }}
    </div>
    @endif
    @endif
</div>
@endsection