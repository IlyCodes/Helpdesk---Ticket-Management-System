<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Help Desk') }}</title>

    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="{{ asset('images/logo.svg') }}">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />

    <!-- Scripts & Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        [x-cloak] {
            display: none !important;
        }
    </style>
</head>

<body class="font-sans antialiased text-gray-900 bg-gray-50">
    <div class="min-h-screen">
        <!-- Header & Navigation -->
        <header class="bg-white/80 backdrop-blur-md sticky top-0 z-40">
            <nav class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center h-20">
                    <!-- Logo -->
                    <a href="{{ route('welcome') }}" class="flex items-center space-x-2">
                        <x-application-logo class="w-12 h-12" />
                        <span class="font-bold text-2xl text-gray-800">{{ config('app.name', 'HelpDesk') }}</span>
                    </a>

                    <!-- Auth Links -->
                    <div class="flex items-center space-x-4">
                        @auth
                        <a href="{{ route('dashboard') }}" class="px-6 py-2 text-sm font-semibold text-white bg-indigo-600 rounded-lg shadow-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition-colors">Dashboard</a>
                        @else
                        <a href="{{ route('login') }}" class="text-sm font-semibold text-gray-600 hover:text-indigo-600 transition-colors">Log in</a>
                        @if (Route::has('register'))
                        <a href="{{ route('register') }}" class="hidden sm:inline-block px-5 py-2 text-sm font-semibold text-white bg-indigo-600 rounded-lg shadow-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition-colors">Register</a>
                        @endif
                        @endauth
                    </div>
                </div>
            </nav>
        </header>

        <!-- Main Content -->
        <main>
            <!-- Hero Section -->
            <section class="relative bg-white pt-20 pb-24 sm:pt-28 sm:pb-32">
                <div class="absolute inset-0 overflow-hidden">
                    <div class="absolute top-0 left-0 w-full h-full bg-gradient-to-br from-indigo-50 via-white to-blue-50"></div>
                    <svg class="absolute top-0 right-0 -mr-48 -mt-48 w-[80rem] h-[80rem] text-indigo-100/50" fill="currentColor" viewBox="0 0 1024 1024">
                        <circle cx="512" cy="512" r="512" />
                    </svg>
                </div>
                <div class="relative max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
                    <h1 class="text-4xl sm:text-5xl lg:text-6xl font-extrabold text-gray-900 tracking-tight">
                        Reliable Support, Instant Solutions.
                    </h1>
                    <p class="mt-6 text-lg sm:text-xl text-gray-600 max-w-2xl mx-auto">
                        Welcome to our support center. Get help with your issues quickly, track your requests with ease, or find answers instantly in our knowledge base.
                    </p>
                    <div class="mt-10 flex justify-center space-x-4">
                        <a href="#knowledge-base" class="px-8 py-3 text-base font-semibold text-white bg-indigo-600 rounded-lg shadow-lg hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transform transition-transform hover:scale-105">
                            Find Answers
                        </a>
                        @guest
                        <a href="{{ route('register') }}" class="px-8 py-3 text-base font-semibold text-indigo-700 bg-indigo-100 rounded-lg hover:bg-indigo-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transform transition-transform hover:scale-105">
                            Get Started
                        </a>
                        @endguest
                    </div>
                </div>
            </section>

            <!-- Features Section -->
            <section class="py-20 sm:py-24">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="text-center">
                        <h2 class="text-3xl font-bold text-gray-900 tracking-tight">Everything You Need for Effortless Support</h2>
                        <p class="mt-4 text-lg text-gray-600">Our platform is designed to make your support experience seamless.</p>
                    </div>
                    <div class="mt-16 grid grid-cols-1 md:grid-cols-3 gap-12 text-center">
                        <div class="flex flex-col items-center">
                            <div class="flex items-center justify-center h-16 w-16 rounded-full bg-indigo-100 text-indigo-600">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-8 h-8">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 12h16.5m-16.5 3.75h16.5M3.75 19.5h16.5M5.625 4.5h12.75a1.875 1.875 0 010 3.75H5.625a1.875 1.875 0 010-3.75z" />
                                </svg>
                            </div>
                            <h3 class="mt-6 text-xl font-semibold">Effortless Ticketing</h3>
                            <p class="mt-2 text-base text-gray-600">Quickly submit support tickets with all the necessary details and attachments.</p>
                        </div>
                        <div class="flex flex-col items-center">
                            <div class="flex items-center justify-center h-16 w-16 rounded-full bg-indigo-100 text-indigo-600">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-8 h-8">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0011.667 0l3.182-3.182m0-4.991v4.99" />
                                </svg>
                            </div>
                            <h3 class="mt-6 text-xl font-semibold">Track Your Progress</h3>
                            <p class="mt-2 text-base text-gray-600">Stay informed with real-time status updates and a complete history of your requests.</p>
                        </div>
                        <div class="flex flex-col items-center">
                            <div class="flex items-center justify-center h-16 w-16 rounded-full bg-indigo-100 text-indigo-600">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-8 h-8">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                                </svg>
                            </div>
                            <h3 class="mt-6 text-xl font-semibold">Instant Answers</h3>
                            <p class="mt-2 text-base text-gray-600">Search our comprehensive knowledge base to find solutions to common issues instantly.</p>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Knowledge Base / FAQ Section -->
            <section id="knowledge-base" class="py-20 sm:py-24 bg-white">
                <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8"
                    x-data="{ 
                        search: '',
                        faqs: {{ $faqs->map(fn($faq) => ['id' => $faq->id, 'question' => $faq->question, 'answer' => $faq->answer, 'keywords' => $faq->keywords ?? []]) }},
                        activeAccordion: null,
                        get filteredFaqs() {
                            if (this.search.trim() === '') {
                                return this.faqs;
                            }
                            return this.faqs.filter(
                                faq => 
                                    faq.question.toLowerCase().includes(this.search.toLowerCase()) ||
                                    faq.answer.toLowerCase().includes(this.search.toLowerCase()) ||
                                    faq.keywords.some(keyword => keyword.toLowerCase().includes(this.search.toLowerCase()))
                            );
                        },
                        toggleAccordion(id) {
                            this.activeAccordion = this.activeAccordion === id ? null : id;
                        }
                     }">
                    <div class="text-center">
                        <h2 class="text-3xl font-bold text-gray-900 tracking-tight">Knowledge Base</h2>
                        <p class="mt-4 text-lg text-gray-600">Have a question? Find answers to our most frequently asked questions below.</p>
                    </div>

                    <!-- Search Bar -->
                    <div class="mt-12 relative">
                        <input type="search" x-model.debounce.300ms="search" placeholder="Search for answers..."
                            class="w-full pl-12 pr-4 py-3 text-lg border border-gray-300 rounded-full shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        <div class="absolute inset-y-0 left-0 flex items-center pl-4 pointer-events-none">
                            <svg class="w-6 h-6 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                            </svg>
                        </div>
                    </div>

                    <!-- FAQ List -->
                    <div class="mt-10 space-y-4">
                        <template x-for="faq in filteredFaqs" :key="faq.id">
                            <div class="bg-white border border-gray-200 rounded-lg shadow-sm overflow-hidden">
                                <button @click="toggleAccordion(faq.id)" class="w-full flex justify-between items-center text-left p-5 font-semibold text-gray-800 hover:bg-gray-50 focus:outline-none">
                                    <span x-text="faq.question"></span>
                                    <svg class="w-6 h-6 transition-transform duration-300" :class="{ 'rotate-180': activeAccordion === faq.id }" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                                    </svg>
                                </button>
                                <div x-show="activeAccordion === faq.id" x-collapse x-cloak>
                                    <div class="p-5 pt-0 text-gray-600 prose" x-html="faq.answer"></div>
                                </div>
                            </div>
                        </template>

                        <div x-show="filteredFaqs.length === 0" class="text-center py-10 text-gray-500" x-cloak>
                            <p class="font-semibold">No results found for "<span x-text="search"></span>".</p>
                            <p class="mt-2">Try a different search term or create a support ticket.</p>
                        </div>
                    </div>
                </div>
            </section>
        </main>
    </div>

    <!-- Footer -->
    <footer class="bg-gray-100 border-t border-gray-200">
        <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8 text-center text-sm text-gray-500">
            &copy; {{ date('Y') }} {{ config('app.name', 'HelpDesk') }}. All rights reserved.
            {{-- You can add more footer links here if you want --}}
        </div>
    </footer>
</body>

</html>