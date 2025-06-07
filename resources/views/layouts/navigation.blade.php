<nav x-data="{ open: false, openUserMenu: false, openNotificationsMenu: false }" class="bg-white border-b border-gray-200 shadow-sm">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('welcome') }}" class="flex items-center text-gray-800 hover:text-gray-900">
                        {{-- Application Logo --}}
                        <x-application-logo class="w-12 h-12" />
                        <span class="ml-2 font-bold text-xl text-gray-800">{{ config('app.name', 'Help Desk') }}</span>
                    </a>
                </div>

                <div class="hidden space-x-8 sm:-my-px sm:ml-10 sm:flex">
                    @auth
                    @if(Auth::user()->isClient())
                    <x-nav-link :href="route('client.tickets.index')" :active="request()->routeIs('client.tickets.*')">
                        {{ __('My Tickets') }}
                    </x-nav-link>
                    <x-nav-link :href="route('client.tickets.create')" :active="request()->routeIs('client.tickets.create')">
                        {{ __('New Ticket') }}
                    </x-nav-link>
                    @endif

                    @if(Auth::user()->isAgent())
                    <x-nav-link :href="route('agent.tickets.index')" :active="request()->routeIs('agent.tickets.*')">
                        {{ __('Assigned Tickets') }}
                    </x-nav-link>
                    @endif

                    @if(Auth::user()->isAdmin())
                    <x-nav-link :href="route('admin.dashboard')" :active="request()->routeIs('admin.dashboard')">
                        {{ __('Dashboard') }}
                    </x-nav-link>
                    <x-nav-link :href="route('admin.tickets.index')" :active="request()->routeIs('admin.tickets.*')">
                        {{ __('All Tickets') }}
                    </x-nav-link>
                    <x-nav-link :href="route('admin.users.index')" :active="request()->routeIs('admin.users.*')">
                        {{ __('User Management') }}
                    </x-nav-link>
                    <x-nav-link :href="route('admin.faqs.index')" :active="request()->routeIs('admin.faqs.*')"> {{-- New Link --}}
                        {{ __('Manage FAQs') }}
                    </x-nav-link>
                    @endif
                    @endauth
                </div>
            </div>

            <div class="hidden sm:flex sm:items-center sm:ml-6">
                @auth
                <div class="relative mr-3">
                    <button @click="openNotificationsMenu = !openNotificationsMenu; if(openNotificationsMenu) { fetchNotifications(); markNotificationsAsReadOnServer(); }"
                        id="notificationBellButton" aria-label="Notifications"
                        class="p-1 rounded-full text-gray-400 hover:text-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 relative">
                        <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                        </svg>
                        <span id="notificationBellDot" class="absolute top-0 right-0 block h-2.5 w-2.5 transform translate-x-1/2 -translate-y-1/4 bg-red-500 rounded-full ring-2 ring-white" style="display: {{ Auth::user()->unreadNotifications->count() > 0 ? 'block' : 'none' }};"></span>
                    </button>

                    <div x-show="openNotificationsMenu"
                        @click.away="openNotificationsMenu = false"
                        x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                        x-transition:leave="transition ease-in duration-75" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
                        class="absolute z-50 mt-2 w-80 max-h-96 overflow-y-auto rounded-md shadow-lg origin-top-right right-0 sm:right-auto sm:left-1/2 sm:-translate-x-full" {{-- Adjusted positioning --}}
                        style="display: none;" x-cloak>
                        <div class="rounded-md ring-1 ring-black ring-opacity-5 bg-white">
                            <div class="px-4 py-3 border-b flex justify-between items-center">
                                <span class="text-sm font-medium text-gray-900">Notifications</span>
                                {{-- Optional: Add a "Mark all as read" button here if desired --}}
                            </div>
                            <div id="notification-items" class="py-1 divide-y divide-gray-100">
                                {{-- Populated by JS --}}
                                <div class="p-4 text-sm text-gray-500 text-center">Loading notifications...</div>
                            </div>
                            <div class="px-4 py-2 border-t text-center bg-gray-50">
                                <a href="{{ route('notifications.index') }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-800">
                                    View All Notifications
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="relative">
                    <button @click="openUserMenu = !openUserMenu" class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none transition ease-in-out duration-150">
                        <div>{{ Auth::user()->name }}</div>
                        <div class="ml-1">
                            <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                            </svg>
                        </div>
                    </button>
                    <div x-show="openUserMenu"
                        @click.away="openUserMenu = false"
                        x-transition:enter="transition ease-out duration-100" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                        x-transition:leave="transition ease-in duration-75" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
                        class="absolute z-50 mt-2 w-48 rounded-md shadow-lg origin-top-right right-0"
                        style="display: none;" x-cloak>
                        <div class="rounded-md ring-1 ring-black ring-opacity-5 py-1 bg-white">
                            <x-dropdown-link :href="route('profile.edit')">
                                {{ __('Profile') }}
                            </x-dropdown-link>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <x-dropdown-link :href="route('logout')"
                                    onclick="event.preventDefault();
                                                        this.closest('form').submit();">
                                    {{ __('Log Out') }}
                                </x-dropdown-link>
                            </form>
                        </div>
                    </div>
                </div>
                @else
                <a href="{{ route('login') }}" class="text-sm text-gray-700 hover:text-gray-900 font-medium">Log in</a>
                @if (Route::has('register'))
                <a href="{{ route('register') }}" class="ml-4 text-sm text-gray-700 hover:text-gray-900 font-medium">Register</a>
                @endif
                @endauth
            </div>

            <div class="-mr-2 flex items-center sm:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-gray-500 transition duration-150 ease-in-out">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden">
        <div class="pt-2 pb-3 space-y-1">
            @auth
            @if(Auth::user()->isClient())
            <x-responsive-nav-link :href="route('client.tickets.index')" :active="request()->routeIs('client.tickets.*')">
                {{ __('My Tickets') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('client.tickets.create')" :active="request()->routeIs('client.tickets.create')">
                {{ __('New Ticket') }}
            </x-responsive-nav-link>
            @endif
            @if(Auth::user()->isAgent())
            <x-responsive-nav-link :href="route('agent.tickets.index')" :active="request()->routeIs('agent.tickets.*')">
                {{ __('Assigned Tickets') }}
            </x-responsive-nav-link>
            @endif
            @if(Auth::user()->isAdmin())
            <x-responsive-nav-link :href="route('admin.dashboard')" :active="request()->routeIs('admin.dashboard')">
                {{ __('Dashboard') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('admin.tickets.index')" :active="request()->routeIs('admin.tickets.*')">
                {{ __('All Tickets') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('admin.users.index')" :active="request()->routeIs('admin.users.*')">
                {{ __('User Management') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('admin.faqs.index')" :active="request()->routeIs('admin.faqs.*')">
                {{ __('Manage FAQs') }}
            </x-responsive-nav-link>
            @endif
            @endauth
        </div>

        @auth
        <div class="pt-4 pb-1 border-t border-gray-200">
            <div class="px-4">
                <div class="font-medium text-base text-gray-800">{{ Auth::user()->name }}</div>
                <div class="font-medium text-sm text-gray-500">{{ Auth::user()->email }}</div>
            </div>

            <div class="mt-3 space-y-1">
                <x-responsive-nav-link :href="route('profile.edit')">
                    {{ __('Profile') }}
                </x-responsive-nav-link>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <x-responsive-nav-link :href="route('logout')"
                        onclick="event.preventDefault();
                                            this.closest('form').submit();">
                        {{ __('Log Out') }}
                    </x-responsive-nav-link>
                </form>
            </div>
        </div>
        @else
        <div class="py-1 border-t border-gray-200">
            <x-responsive-nav-link :href="route('login')" :active="request()->routeIs('login')">
                {{ __('Log in') }}
            </x-responsive-nav-link>
            @if (Route::has('register'))
            <x-responsive-nav-link :href="route('register')" :active="request()->routeIs('register')">
                {{ __('Register') }}
            </x-responsive-nav-link>
            @endif
        </div>
        @endauth
    </div>
</nav>