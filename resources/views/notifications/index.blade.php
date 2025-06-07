@extends('layouts.app')

@section('title', 'My Notifications')

@section('content')
<div class="bg-white shadow-md overflow-hidden sm:rounded-lg">
    <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
        <div class="flex flex-col sm:flex-row justify-between items-center gap-4">
            <h2 class="text-xl lg:text-2xl font-semibold text-gray-800 leading-tight">Notifications</h2>

            {{-- Action Buttons --}}
            <div class="flex items-center space-x-2">
                <form action="{{ route('notifications.markAllAsRead') }}" method="POST" onsubmit="return confirm('Are you sure you want to mark all notifications as read?');">
                    @csrf
                    <button type="submit" class="px-3 py-1.5 text-xs font-medium text-gray-600 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50">
                        Mark All as Read
                    </button>
                </form>
                <form action="{{ route('notifications.destroyAll') }}" method="POST" onsubmit="return confirm('Are you sure you want to delete ALL notifications? This cannot be undone.');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="px-3 py-1.5 text-xs font-medium text-red-600 bg-red-50 border border-red-200 rounded-md shadow-sm hover:bg-red-100">
                        Delete All
                    </button>
                </form>
            </div>
        </div>
    </div>

    @if($notifications->isEmpty())
    <div class="p-6 text-center text-gray-500">
        You have no notifications.
    </div>
    @else
    <ul role="list" class="divide-y divide-gray-200">
        @foreach ($notifications as $notification)
        <li class="{{ $notification->read_at ? 'bg-white' : 'bg-indigo-50' }} hover:bg-gray-50">
            <a href="{{ $notification->data['url'] ?? '#' }}" class="block">
                <div class="px-4 py-4 sm:px-6">
                    <div class="flex items-center justify-between">
                        <p class="text-sm font-medium text-indigo-600 truncate">
                            @if(isset($notification->data['icon'])) <i class="{{ $notification->data['icon'] }} mr-2"></i> @endif
                            {{ $notification->data['message'] ?? 'Notification' }}
                        </p>
                        <div class="ml-2 flex-shrink-0 flex">
                            @if(!$notification->read_at)
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                New
                            </span>
                            @endif
                        </div>
                    </div>
                    <div class="mt-2 sm:flex sm:justify-between">
                        <div class="sm:flex">
                            {{-- You can add more details from notification->data if needed --}}
                        </div>
                        <div class="mt-2 flex items-center text-sm text-gray-500 sm:mt-0">
                            <svg class="flex-shrink-0 mr-1.5 h-5 w-5 text-gray-400" xmlns="[http://www.w3.org/2000/svg](http://www.w3.org/2000/svg)" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd" />
                            </svg>
                            <p>
                                <time datetime="{{ $notification->created_at->toIso8601String() }}">{{ $notification->created_at->diffForHumans() }}</time>
                            </p>
                        </div>
                    </div>
                </div>
            </a>
        </li>
        @endforeach
    </ul>
    @if($notifications->hasPages())
    <div class="px-4 py-3 border-t border-gray-200 sm:px-6 bg-white">
        {{ $notifications->links() }}
    </div>
    @endif
    @endif
</div>
@endsection