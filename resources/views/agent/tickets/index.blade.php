@extends('layouts.app')

@section('title', 'My Assigned Tickets')

@section('content')
<div class="bg-white shadow-md overflow-hidden sm:rounded-lg">
    <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
        <div class="flex flex-col sm:flex-row justify-between items-center gap-4">
            <h2 class="text-xl lg:text-2xl font-semibold text-gray-800 leading-tight">My Assigned Tickets</h2>
            <form method="GET" action="{{ route('agent.tickets.index') }}" class="flex items-center gap-2">
                <select name="status" id="status" class="block w-full sm:w-auto rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 text-sm">
                    <option value="">All Statuses</option>
                    @foreach($statuses as $status)
                    <option value="{{ $status->slug }}" {{ request('status') == $status->slug ? 'selected' : '' }}>
                        {{ $status->name }}
                    </option>
                    @endforeach
                </select>
                <button type="submit" class="px-3 py-2 bg-gray-200 text-gray-700 text-xs font-medium rounded-md hover:bg-gray-300">Filter</button>
                @if(request('status'))
                <a href="{{ route('agent.tickets.index') }}" class="px-3 py-2 text-indigo-600 text-xs font-medium rounded-md hover:text-indigo-800">Clear</a>
                @endif
            </form>
        </div>
    </div>

    @if($tickets->isEmpty())
    <div class="p-6 text-center text-gray-500">
        You have no tickets assigned to you{{ request('status') ? ' with the status "'. Str::title(str_replace('-', ' ', request('status'))) .'"' : '' }}.
    </div>
    @else
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        ID
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Title
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hidden sm:table-cell">
                        Client
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Status
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hidden md:table-cell">
                        Priority
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hidden lg:table-cell">
                        Last Updated
                    </th>
                    <th scope="col" class="relative px-6 py-3">
                        <span class="sr-only">View</span>
                    </th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @foreach ($tickets as $ticket)
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                        #{{ $ticket->id }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                        <a href="{{ route('agent.tickets.show', $ticket) }}" class="hover:text-indigo-600 font-medium">
                            {{ Str::limit($ticket->title, 35) }}
                        </a>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 hidden sm:table-cell">
                        {{ $ticket->user->name }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $ticket->status->color_class ?? 'bg-gray-100 text-gray-800' }}">
                            {{ $ticket->status->name }}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 hidden md:table-cell">
                        {{ ucfirst($ticket->priority) }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 hidden lg:table-cell">
                        {{ $ticket->updated_at->format('M d, Y H:i') }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                        <a href="{{ route('agent.tickets.show', $ticket) }}" class="text-indigo-600 hover:text-indigo-900">View</a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @if($tickets->hasPages())
    <div class="px-4 py-3 border-t border-gray-200 sm:px-6 bg-white">
        {{ $tickets->appends(request()->query())->links() }}
    </div>
    @endif
    @endif
</div>
@endsection