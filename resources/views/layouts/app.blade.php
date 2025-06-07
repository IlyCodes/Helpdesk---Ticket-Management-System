<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }} - @yield('title')</title>

    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="{{ asset('images/logo.svg') }}">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    @livewireStyles
    <style>
        body {
            font-family: 'Figtree', sans-serif;
        }

        [x-cloak] {
            display: none !important;
        }
    </style>

    @vite(['resources/css/app.css'])

    @stack('styles')
</head>

<body class="font-sans antialiased">
    <div class="min-h-screen bg-gray-100">
        @include('layouts.navigation')

        <!-- Page Heading -->
        @if (isset($header))
        <header class="bg-white shadow">
            <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                {{ $header }}
            </div>
        </header>
        @endif

        <!-- Page Content -->
        <main class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                @if (session('success'))
                <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded">
                    {{ session('success') }}
                </div>
                @endif
                @if (session('error'))
                <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded">
                    {{ session('error') }}
                </div>
                @endif
                @if ($errors->any())
                <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded">
                    <ul class="list-disc pl-5">
                        @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif
                {{ $slot ?? '' }}
                @yield('content')
        </main>
    </div>

    {{-- <--- The chatbot widget here for authenticated users --}}
    @auth
        @if(Auth::user()->isClient())
            <x-chatbot-widget />
        @endif
    @endauth

    @vite(['resources/js/app.js'])

    @livewireScripts
    @stack('scripts')

    @auth
    <script>
        let unreadNotificationsCount = {{Auth::user()->unreadNotifications->count()}};
        const bellIcon = document.querySelector('button[aria-label="Notifications"]'); // More robust selector needed if multiple buttons

        function fetchNotifications() {
            fetch('{{ route("notifications.recent") }}') // We will create this route
                .then(response => response.json())
                .then(data => {
                    const container = document.getElementById('notification-items');
                    container.innerHTML = ''; // Clear current items
                    if (data.notifications.length > 0) {
                        data.notifications.forEach(notification => {
                            const item = document.createElement('a');
                            item.href = notification.data.url || '#';
                            item.classList.add('block', 'px-4', 'py-2', 'text-sm', 'text-gray-700', 'hover:bg-gray-100');
                            item.innerHTML = `<div class="font-medium">${notification.data.message || 'New Notification'}</div>
                                              <div class="text-xs text-gray-500">${new Date(notification.created_at).toLocaleString()}</div>`;
                            container.appendChild(item);
                        });
                    } else {
                        container.innerHTML = '<div class="p-4 text-sm text-gray-500">No new notifications.</div>';
                    }
                    updateBellIcon(data.unread_count);
                })
                .catch(error => console.error('Error fetching notifications:', error));
        }

        function markNotificationsAsRead() {
            // Only send if there are unread notifications to avoid unnecessary requests
            if (unreadNotificationsCount > 0) {
                fetch('{{ route("notifications.markAsRead") }}', { // We will create this route
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'Accept': 'application/json',
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            updateBellIcon(0); // Update count on client side immediately
                            unreadNotificationsCount = 0;
                        }
                    })
                    .catch(error => console.error('Error marking notifications as read:', error));
            }
        }

        function updateBellIcon(count) {
            const bellSpan = document.querySelector('button[aria-label="Notifications"] span'); // Adjust selector as needed
            if (bellSpan) {
                if (count > 0) {
                    bellSpan.style.display = 'block'; // Show red dot
                } else {
                    bellSpan.style.display = 'none'; // Hide red dot
                }
            }
            unreadNotificationsCount = count; // Update global count
        }

        // Optional: Periodically check for new notifications
        // setInterval(fetchNotifications, 60000); // every 60 seconds

        // Initial fetch if needed or rely on bell click
        // fetchNotifications(); // If you want to load them immediately
    </script>
    @endauth
</body>

</html>