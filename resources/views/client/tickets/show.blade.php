@extends('layouts.app')

@section('title', 'Ticket #' . $ticket->id . ': ' . $ticket->title)

@section('content')
<div class="bg-white shadow overflow-hidden sm:rounded-lg">
    <div class="px-4 py-5 sm:px-6">
        <div class="flex items-start justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-gray-900 leading-tight">
                    <span class="text-gray-500">Ticket #{{ $ticket->id }}:</span> {{ $ticket->title }}
                </h1>
                <p class="mt-1 text-sm text-gray-600">
                    Submitted by {{ $ticket->user->name }} on {{ $ticket->created_at->format('M d, Y \a\t h:i A') }}
                </p>
            </div>
            <span class="px-3 py-1 inline-flex text-sm leading-5 font-semibold rounded-full {{ $ticket->status->color_class ?? 'bg-gray-100 text-gray-800' }}">
                {{ $ticket->status->name }}
            </span>
        </div>
    </div>

    <div class="border-t border-gray-200 px-4 py-5 sm:p-0">
        <dl class="sm:divide-y sm:divide-gray-200">
            <div class="py-3 sm:py-4 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                <dt class="text-sm font-medium text-gray-500">Category</dt>
                <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">{{ $ticket->category->name }}</dd>
            </div>
            <div class="py-3 sm:py-4 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                <dt class="text-sm font-medium text-gray-500">Priority</dt>
                <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">{{ ucfirst($ticket->priority) }}</dd>
            </div>
            @if($ticket->agent)
            <div class="py-3 sm:py-4 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                <dt class="text-sm font-medium text-gray-500">Assigned Agent</dt>
                <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">{{ $ticket->agent->name }}</dd>
            </div>
            @endif
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
                        <li class="pl-3 pr-4 py-3 flex items-center justify-between text-sm">
                            <div class="w-0 flex-1 flex items-center">
                                <svg class="flex-shrink-0 h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m18.375 12.739-7.693 7.693a4.5 4.5 0 0 1-6.364-6.364l10.94-10.94A3 3 0 1 1 19.5 7.372L8.552 18.32m.009-.01-.01.01m5.699-9.941-7.81 7.81a1.5 1.5 0 0 0 2.112 2.13" />
                                </svg>
                                <span class="ml-2 flex-1 w-0 truncate">{{ $attachment->original_name }}</span>
                            </div>
                            <div class="ml-4 flex-shrink-0">
                                <a href="{{ Storage::url($attachment->file_path) }}" target="_blank" class="font-medium text-indigo-600 hover:text-indigo-500">View</a>
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
            @if(!$reply->is_internal || (Auth::check() && (Auth::user()->isAgent() || Auth::user()->isAdmin())))
            <div class="flex gap-x-4 {{ $reply->user_id === Auth::id() ? 'justify-end' : '' }}">
                @if($reply->user_id !== Auth::id())
                <img class="h-10 w-10 flex-none rounded-full bg-gray-50" src="[https://ui-avatars.com/api/?name=](https://ui-avatars.com/api/?name=){{ urlencode($reply->user->name) }}&color=7F9CF5&background=EBF4FF" alt="">
                @endif
                <div class="flex-auto rounded-md p-3 ring-1 ring-inset {{ $reply->user_id === Auth::id() ? 'bg-blue-50 ring-blue-200' : ($reply->is_internal ? 'bg-yellow-50 ring-yellow-200' : 'bg-gray-50 ring-gray-200') }}">
                    <div class="flex justify-between gap-x-4">
                        <div class="py-0.5 text-xs leading-5 text-gray-500">
                            <span class="font-medium {{ $reply->user_id === Auth::id() ? 'text-blue-700' : ($reply->user->isAgent() || $reply->user->isAdmin() ? 'text-red-600' : 'text-gray-900')}}">
                                {{ $reply->user->name }}
                                @if($reply->user->isAgent()) (Agent) @endif
                                @if($reply->user->isAdmin()) (Admin) @endif
                            </span>
                            commented
                        </div>
                        <time datetime="{{ $reply->created_at->toIso8601String() }}" class="flex-none py-0.5 text-xs leading-5 text-gray-500">
                            {{ $reply->created_at->diffForHumans() }}
                        </time>
                    </div>
                    @if($reply->is_internal)
                    <p class="text-xs font-semibold text-yellow-700 my-1">[INTERNAL NOTE]</p>
                    @endif
                    <p class="text-sm leading-6 text-gray-700 whitespace-pre-wrap">{{ $reply->body }}</p>
                </div>
                @if($reply->user_id === Auth::id())
                <img class="h-10 w-10 flex-none rounded-full bg-gray-50" src="[https://ui-avatars.com/api/?name=](https://ui-avatars.com/api/?name=){{ urlencode($reply->user->name) }}&color=7F9CF5&background=EBF4FF" alt="">
                @endif
            </div>
            @endif
            @endforeach
        </div>
        @else
        <p class="text-sm text-gray-500">No replies yet.</p>
        @endif
    </div>

    @if(!in_array($ticket->status->slug, ['closed', 'resolved']))
    <div class="border-t border-gray-200 px-4 py-5 sm:px-6">
        <h3 class="text-lg font-medium text-gray-900 mb-3">Add Your Reply</h3>
        <form action="{{ route('client.tickets.reply', $ticket) }}" method="POST">
            @csrf
            <div class="mb-4">
                <textarea name="body" id="body" rows="5" required
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                    placeholder="Type your message here..."></textarea>
                @error('body') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
            <div class="flex justify-end">
                <button type="submit"
                    class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-500 active:bg-blue-700 focus:outline-none focus:border-blue-700 focus:ring ring-blue-300 disabled:opacity-25 transition ease-in-out duration-150">
                    Submit Reply
                </button>
            </div>
        </form>
    </div>
    @else
    <div class="border-t border-gray-200 px-4 py-5 sm:px-6">
        <p class="text-sm text-gray-600 bg-yellow-50 p-3 rounded-md">This ticket is currently {{ $ticket->status->name }} and cannot be replied to. If you need to reopen this ticket, please contact support or create a new ticket.</p>
    </div>
    @endif

    @php
    $isReopenableStatus = in_array($ticket->status->slug, ['resolved', 'closed']);
    $isWithinReopenWindow = $ticket->updated_at->gt(now()->subDays(14));
    @endphp

    @if ($isReopenableStatus && $isWithinReopenWindow)
    <div class="border-t border-gray-200 px-4 py-5 sm:px-6 bg-yellow-50">
        <h3 class="text-lg font-medium text-gray-900 mb-3">Reopen Ticket</h3>
        <p class="text-sm text-gray-600 mb-4">If the issue is not fully resolved, you can reopen this ticket by leaving a comment below.</p>
        <form action="{{ route('client.tickets.reopen', $ticket) }}" method="POST">
            @csrf
            <div class="mb-3">
                <label for="reopen_comment" class="sr-only">Comment for reopening</label>
                <textarea name="reopen_comment" id="reopen_comment" rows="4" required
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                    placeholder="Please describe why you are reopening this ticket..."></textarea>
                @error('reopen_comment') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
            <div class="flex justify-end">
                <button type="submit"
                    class="inline-flex items-center px-4 py-2 bg-yellow-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-yellow-600 active:bg-yellow-700 focus:outline-none focus:border-yellow-900 focus:ring ring-yellow-300 disabled:opacity-25 transition ease-in-out duration-150">
                    Reopen Ticket
                </button>
            </div>
        </form>
    </div>
    @elseif ($isReopenableStatus && !$isWithinReopenWindow)
    <div class="border-t border-gray-200 px-4 py-5 sm:px-6">
        <p class="text-sm text-gray-600 bg-gray-100 p-3 rounded-md">This ticket was closed more than 14 days ago and can no longer be reopened. Please create a new ticket if you are still experiencing issues.</p>
    </div>
    @endif

    <div class="px-4 py-3 bg-gray-50 text-right sm:px-6 border-t border-gray-200">
        <a href="{{ route('client.tickets.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-300 focus:outline-none focus:border-gray-300 focus:ring ring-gray-200 active:bg-gray-300 disabled:opacity-25 transition ease-in-out duration-150">
            Back to My Tickets
        </a>
    </div>
</div>
@endsection