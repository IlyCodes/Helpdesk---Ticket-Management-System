@props(['triggerId' => 'chatWidgetTrigger', 'containerId' => 'chatWidgetContainer'])

<div x-data="chatWidget()" class="fixed bottom-0 right-0 mb-4 mr-4 z-50">
    <button @click="open = !open" id="{{ $triggerId }}"
        class="bg-indigo-600 text-white p-4 rounded-full shadow-lg hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition-transform transform hover:scale-110">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-8 h-8">
            <path stroke-linecap="round" stroke-linejoin="round" d="M8.625 12a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H8.25m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H12m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0h-.375M21 12c0 4.556-3.86 8.25-8.625 8.25S3.75 16.556 3.75 12 7.61 3.75 12.375 3.75 21 7.444 21 12zM12 12" />
        </svg>
    </button>

    <div x-show="open"
        @click.away="open = false"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 transform translate-y-4"
        x-transition:enter-end="opacity-100 transform translate-y-0"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100 transform translate-y-0"
        x-transition:leave-end="opacity-0 transform translate-y-4"
        id="{{ $containerId }}"
        class="absolute bottom-20 right-0 w-80 sm:w-96 bg-white rounded-lg shadow-xl border border-gray-200 flex flex-col"
        style="display: none; height: 60vh; max-height: 500px;" x-cloak>

        <div class="bg-indigo-600 text-white p-3 rounded-t-lg flex justify-between items-center">
            <h3 class="text-lg font-semibold">AI Support Chat</h3>
            <button @click="open = false" class="text-indigo-200 hover:text-white">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <div id="chatMessages" class="flex-1 p-4 space-y-3 overflow-y-auto scrolling-touch">
            <div class="flex">
                <div class="bg-gray-200 text-gray-800 p-3 rounded-lg max-w-xs shadow">
                    <p class="text-sm">Hello! How can I help you today?</p>
                </div>
            </div>
            {{-- Messages will be appended here by JavaScript --}}
        </div>

        <div class="p-3 border-t border-gray-200 bg-gray-50 rounded-b-lg">
            <form @submit.prevent="sendMessage">
                <div class="flex items-center space-x-2">
                    <input type="text" x-model="message"
                        placeholder="Type your message..."
                        :disabled="loading"
                        class="flex-1 w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                        autocomplete="off" x-ref="chatInput">
                    <button type="submit" :disabled="loading || message.trim() === ''"
                        class="inline-flex items-center justify-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed">
                        <span x-show="!loading">Send</span>
                        <span x-show="loading">
                            <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                        </span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('chatWidget', () => ({
                open: false,
                message: '',
                history: [], // Stores { role: 'user'/'model', parts: [{text: '...'}] }
                loading: false,

                init() {
                    // Optional: You can add the initial bot message to history here if you want
                    // this.history.push({ role: 'model', parts: [{ text: 'Hello! How can I help you today?' }] });

                    // Focus input when widget opens
                    this.$watch('open', value => {
                        if (value) {
                            this.$nextTick(() => {
                                this.$refs.chatInput.focus();
                            });
                        }
                    })
                },

                appendMessage(text, sender = 'user', isError = false) {
                    const messagesContainer = document.getElementById('chatMessages');
                    if (!messagesContainer) return;

                    const messageFlexDiv = document.createElement('div');
                    messageFlexDiv.classList.add('flex', sender === 'user' ? 'justify-end' : 'justify-start');

                    const bubbleDiv = document.createElement('div');
                    let bgColor = sender === 'user' ? 'bg-indigo-500' : 'bg-gray-200';
                    let textColor = sender === 'user' ? 'text-white' : 'text-gray-800';
                    if (isError) {
                        bgColor = 'bg-red-100';
                        textColor = 'text-red-700';
                    }

                    bubbleDiv.classList.add(bgColor, textColor, 'p-3', 'rounded-lg', 'max-w-xs', 'shadow', 'text-sm');
                    bubbleDiv.innerHTML = `<p>${this.escapeHtml(text)}</p>`;

                    messageFlexDiv.appendChild(bubbleDiv);
                    messagesContainer.appendChild(messageFlexDiv);
                    messagesContainer.scrollTop = messagesContainer.scrollHeight;
                },

                escapeHtml(unsafe) {
                    if (typeof unsafe !== 'string') return '';
                    return unsafe
                        .replace(/&/g, "&amp;")
                        .replace(/</g, "&lt;")
                        .replace(/>/g, "&gt;")
                        .replace(/"/g, "&quot;")
                        .replace(/'/g, "&#039;");
                },

                async sendMessage() {
                    if (this.message.trim() === '') return;
                    this.loading = true;
                    const userMessageText = this.message;

                    this.appendMessage(userMessageText, 'user');
                    this.history.push({
                        role: 'user',
                        parts: [{
                            text: userMessageText
                        }]
                    });

                    // Keep history to a manageable size to send to API
                    const maxHistoryTurns = 5; // e.g., last 5 user/model pairs
                    const historyToSend = this.history.slice(-(maxHistoryTurns * 2));

                    this.message = ''; // Clear input

                    try {
                        const response = await fetch('{{ route("chatbot.send") }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                                'Accept': 'application/json',
                            },
                            body: JSON.stringify({
                                message: userMessageText, // Current message
                                history: historyToSend.slice(0, -1) // History *before* this current user message
                            })
                        });

                        const data = await response.json();

                        if (response.ok) {
                            this.appendMessage(data.reply, 'bot');
                            this.history.push({
                                role: 'model',
                                parts: [{
                                    text: data.reply
                                }]
                            });
                        } else {
                            this.appendMessage(data.reply || 'Error: Could not get a response.', 'bot', true);
                            this.history.push({
                                role: 'model',
                                parts: [{
                                    text: data.reply || 'Error response from server'
                                }]
                            });
                        }
                    } catch (error) {
                        console.error('Chatbot error:', error);
                        this.appendMessage('Sorry, an error occurred while connecting. Please try again.', 'bot', true);
                        this.history.push({
                            role: 'model',
                            parts: [{
                                text: 'Connection error client-side'
                            }]
                        });
                    } finally {
                        this.loading = false;
                        this.$nextTick(() => { // Re-focus after sending
                            this.$refs.chatInput.focus();
                        });
                    }
                }
            }));
        });
    </script>
</div>