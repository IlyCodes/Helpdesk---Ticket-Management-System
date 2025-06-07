@extends('layouts.app')

@section('title', 'Ticket #' . $ticket->id . ' - Agent View')

@section('content')
<div class="bg-white shadow-md overflow-hidden sm:rounded-lg">
    <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
        <div class="flex flex-col sm:flex-row items-start justify-between gap-3">
            <div>
                <h1 class="text-xl lg:text-2xl font-semibold text-gray-900 leading-tight">
                    <span class="text-gray-500">Ticket #{{ $ticket->id }}:</span> {{ $ticket->title }}
                </h1>
                <p class="mt-1 text-sm text-gray-600">
                    Submitted by <span class="font-medium">{{ $ticket->user->name }} ({{ $ticket->user->email }})</span>
                    on {{ $ticket->created_at->format('M d, Y \a\t h:i A') }}
                </p>
                <p class="mt-1 text-sm text-gray-500">
                    Currently assigned to: <span class="font-medium">{{ $ticket->agent ? $ticket->agent->name : 'Not Assigned' }}</span>
                </p>
            </div>
            <span class="px-3 py-1 inline-flex text-sm leading-5 font-semibold rounded-full {{ $ticket->status->color_class ?? 'bg-gray-100 text-gray-800' }}">
                {{ $ticket->status->name }}
            </span>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-0">
        <div class="md:col-span-2 border-r border-gray-200">
            <div class="px-4 py-5 sm:p-0">
                <dl class="sm:divide-y sm:divide-gray-200">
                    <div class="py-3 sm:py-4 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                        <dt class="text-sm font-medium text-gray-500">Client</dt>
                        <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">{{ $ticket->user->name }}</dd>
                    </div>
                    <div class="py-3 sm:py-4 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                        <dt class="text-sm font-medium text-gray-500">Client Email</dt>
                        <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">{{ $ticket->user->email }}</dd>
                    </div>
                    <div class="py-3 sm:py-4 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                        <dt class="text-sm font-medium text-gray-500">Category</dt>
                        <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">{{ $ticket->category->name }}</dd>
                    </div>
                    <div class="py-3 sm:py-4 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                        <dt class="text-sm font-medium text-gray-500">Priority</dt>
                        <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">{{ ucfirst($ticket->priority) }}</dd>
                    </div>
                    <div class="py-3 sm:py-4 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                        <dt class="text-sm font-medium text-gray-500">Description</dt>
                        <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2 whitespace-pre-wrap">{{ $ticket->description }}</dd>
                    </div>

                    @if($ticket->attachments->isNotEmpty())
                    <div class="py-3 sm:py-4 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                        <dt class="text-sm font-medium text-gray-500">Attachments</dt>
                        <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                            <ul role="list" class="border border-gray-200 rounded-md divide-y divide-gray-200">
                                @foreach ($ticket->attachments as $attachment)
                                <li class="pl-3 pr-4 py-3 flex items-center justify-between text-sm hover:bg-gray-50">
                                    <div class="w-0 flex-1 flex items-center">
                                        <svg class="flex-shrink-0 h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="m18.375 12.739-7.693 7.693a4.5 4.5 0 0 1-6.364-6.364l10.94-10.94A3 3 0 1 1 19.5 7.372L8.552 18.32m.009-.01-.01.01m5.699-9.941-7.81 7.81a1.5 1.5 0 0 0 2.112 2.13" />
                                        </svg>
                                        <span class="ml-2 flex-1 w-0 truncate">{{ $attachment->original_name }} <span class="text-xs text-gray-400">({{ round($attachment->size / 1024, 1) }} KB)</span></span>
                                    </div>
                                    <div class="ml-4 flex-shrink-0">
                                        <a href="{{ Storage::url($attachment->file_path) }}" target="_blank" rel="noopener noreferrer" class="font-medium text-indigo-600 hover:text-indigo-500">View</a>
                                    </div>
                                </li>
                                @endforeach
                            </ul>
                        </dd>
                    </div>
                    @endif
                </dl>
            </div>

            <div class="border-t border-gray-200 px-4 py-5 sm:px-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Discussion</h3>
                @if($ticket->replies->isNotEmpty())
                <div class="space-y-6">
                    @foreach($ticket->replies as $reply)
                    <div class="flex gap-x-3 {{ $reply->user_id === Auth::id() ? 'flex-row-reverse' : '' }}">
                        <img class="h-8 w-8 flex-none rounded-full bg-gray-200 object-cover" src="[https://ui-avatars.com/api/?name=](https://ui-avatars.com/api/?name=){{ urlencode($reply->user->name) }}&color=7F9CF5&background=EBF4FF" alt="{{ $reply->user->name }}">
                        <div class="flex-auto rounded-md p-3 ring-1 ring-inset {{ $reply->user_id === Auth::id() ? 'bg-indigo-50 ring-indigo-200' : ($reply->is_internal ? 'bg-yellow-50 ring-yellow-300' : 'bg-gray-50 ring-gray-200') }}">
                            <div class="flex justify-between items-baseline gap-x-2">
                                <p class="text-sm font-semibold {{ $reply->user_id === Auth::id() ? 'text-indigo-700' : ($reply->user->isAgent() || $reply->user->isAdmin() ? 'text-red-600' : 'text-gray-900')}}">
                                    {{ $reply->user->name }}
                                    @if($reply->user->isClient()) <span class="text-xs font-normal text-blue-500">(Client)</span> @endif
                                    @if($reply->user->isAgent() && $reply->user_id !== Auth::id()) <span class="text-xs font-normal text-red-500">(Agent)</span> @endif
                                    @if($reply->user->isAdmin()) <span class="text-xs font-normal text-purple-500">(Admin)</span> @endif
                                </p>
                                <time datetime="{{ $reply->created_at->toIso8601String() }}" class="flex-none text-xs text-gray-500">
                                    {{ $reply->created_at->diffForHumans() }}
                                </time>
                            </div>
                            @if($reply->is_internal)
                            <p class="text-xs font-semibold text-yellow-700 my-1">[INTERNAL NOTE]</p>
                            @endif
                            <p class="mt-1 text-sm leading-6 text-gray-700 whitespace-pre-wrap">{{ $reply->body }}</p>
                        </div>
                    </div>
                    @endforeach
                </div>
                @else
                <p class="text-sm text-gray-500">No replies yet.</p>
                @endif
            </div>
        </div>

        <div class="md:col-span-1 p-6 space-y-6">
            @if ($ticket->status->slug !== 'closed')
            <div>
                <h3 class="text-lg font-medium text-gray-900 mb-3">Change Ticket Status</h3>
                <form action="{{ route('agent.tickets.updateStatus', $ticket) }}" method="POST">
                    @csrf
                    @method('PATCH')
                    <div class="mb-3">
                        <label for="status_id" class="sr-only">Status</label>
                        <select name="status_id" id="status_id" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            @foreach($statuses as $statusOption)
                            @if(in_array($statusOption->slug, ['in-progress', 'pending', 'resolved']))
                            <option value="{{ $statusOption->id }}" {{ $ticket->status_id == $statusOption->id ? 'selected' : '' }}>
                                {{ $statusOption->name }}
                            </option>
                            @endif
                            @endforeach
                        </select>
                        @error('status_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <button type="submit" class="w-full inline-flex justify-center items-center px-4 py-2 bg-yellow-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-yellow-600 active:bg-yellow-700 focus:outline-none focus:border-yellow-700 focus:ring ring-yellow-300 disabled:opacity-25 transition ease-in-out duration-150">
                        Update Status
                    </button>
                </form>
            </div>
            @else
            {{-- Show this message if the ticket is closed --}}
            <div class="rounded-md bg-gray-100 p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-gray-700">Ticket Closed</p>
                        <p class="mt-1 text-sm text-gray-600">This ticket has been closed and can no longer be modified.</p>
                    </div>
                </div>
            </div>
            @endif

            <hr class="my-6">

            <div>
                <h3 class="text-lg font-medium text-gray-900 mb-3">Add Reply / Internal Note</h3>
                <form action="{{ route('agent.tickets.reply', $ticket) }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label for="body" class="sr-only">Reply Body</label>
                        <textarea name="body" id="body" rows="5" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" placeholder="Type your message here..."></textarea>
                        @error('body') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div class="mb-3">
                        <label for="is_internal" class="inline-flex items-center">
                            <input type="checkbox" name="is_internal" id="is_internal" value="1" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                            <span class="ml-2 text-sm text-gray-600">Internal Note (not visible to client)</span>
                        </label>
                    </div>
                    <button type="submit" class="w-full inline-flex justify-center items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-800 focus:outline-none focus:border-indigo-900 focus:ring ring-indigo-300 disabled:opacity-25 transition ease-in-out duration-150">
                        Submit Reply
                    </button>
                </form>
            </div>
        </div>
    </div>

    <div class="px-4 py-3 bg-gray-50 text-right sm:px-6 border-t border-gray-200">
        <a href="{{ route('agent.tickets.index') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
            Back to Assigned Tickets
        </a>
    </div>
</div>
@endsection