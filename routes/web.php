<?php

use App\Http\Controllers\Admin\AdminFaqController;
use App\Http\Controllers\AdminTicketController;
use App\Http\Controllers\AdminUserController;
use App\Http\Controllers\AgentTicketController;
use App\Http\Controllers\ChatbotController;
use App\Http\Controllers\ClientTicketController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\WelcomeController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', WelcomeController::class)->name('welcome');

Route::get('/dashboard', function () {
    // Redirect based on role
    if (Auth::check()) {
        if (Auth::user()->isAdmin()) {
            return redirect()->route('admin.dashboard');
        } elseif (Auth::user()->isAgent()) {
            return redirect()->route('agent.tickets.index');
        } elseif (Auth::user()->isClient()) {
            return redirect()->route('client.tickets.index');
        }
    }
    return redirect()->route('login'); // Fallback if no role matches or not logged in
})->middleware(['auth'])->name('dashboard');

// Client Ticket Routes
Route::middleware(['auth', 'role:client'])->prefix('client')->name('client.')->group(function () {
    Route::get('/tickets', [ClientTicketController::class, 'index'])->name('tickets.index');
    Route::get('/tickets/create', [ClientTicketController::class, 'create'])->name('tickets.create');
    Route::post('/tickets', [ClientTicketController::class, 'store'])->name('tickets.store');
    Route::get('/tickets/{ticket}', [ClientTicketController::class, 'show'])->name('tickets.show');
    Route::post('/tickets/{ticket}/reply', [ClientTicketController::class, 'storeReply'])->name('tickets.reply');
    Route::post('/tickets/{ticket}/reopen', [ClientTicketController::class, 'reopen'])->name('tickets.reopen');
    Route::get('/tickets/{ticket}/close', [ClientTicketController::class, 'close'])->name('tickets.close');
});

// Agent Ticket Routes
Route::middleware(['auth', 'role:agent'])->prefix('agent')->name('agent.')->group(function () {
    Route::get('/tickets', [AgentTicketController::class, 'index'])->name('tickets.index');
    Route::get('/tickets/{ticket}', [AgentTicketController::class, 'show'])->name('tickets.show');
    Route::patch('/tickets/{ticket}/status', [AgentTicketController::class, 'updateStatus'])->name('tickets.updateStatus');
    Route::post('/tickets/{ticket}/reply', [AgentTicketController::class, 'storeReply'])->name('tickets.reply');
});

// Admin Routes
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    // Admin Dashboard
    Route::get('/dashboard', [AdminTicketController::class, 'dashboard'])->name('dashboard');

    // Admin Ticket Management
    Route::get('/tickets', [AdminTicketController::class, 'index'])->name('tickets.index');
    Route::get('/tickets/{ticket}', [AdminTicketController::class, 'show'])->name('tickets.show');
    Route::patch('/tickets/{ticket}/assign', [AdminTicketController::class, 'assignAgent'])->name('tickets.assignAgent');
    Route::patch('/tickets/{ticket}/status', [AdminTicketController::class, 'updateStatus'])->name('tickets.updateStatus'); // Admin can also update status
    Route::post('/tickets/{ticket}/reply', [AdminTicketController::class, 'storeReply'])->name('tickets.reply'); // Admin can also reply

    // User Management Routes (will be added later)
    Route::get('/users', [AdminUserController::class, 'index'])->name('users.index');
    Route::get('/users/create', [AdminUserController::class, 'create'])->name('users.create');
    Route::post('/users', [AdminUserController::class, 'store'])->name('users.store');
    Route::get('/users/{user}/edit', [AdminUserController::class, 'edit'])->name('users.edit');
    Route::put('/users/{user}', [AdminUserController::class, 'update'])->name('users.update');
    Route::delete('/users/{user}', [AdminUserController::class, 'destroy'])->name('users.destroy');

    // Admin FAQ Management Routes
    Route::resource('faqs', AdminFaqController::class)->except(['show']);
    // We can add show route separately if needed, or just rely on edit/index
    // Route::get('faqs/{faq}', [AdminFaqController::class, 'show'])->name('faqs.show');

    // Admin Ticket PDF Download
    Route::get('/tickets/{ticket}/pdf', [AdminTicketController::class, 'downloadPDF'])->name('tickets.pdf');

    // New route for AI reassignment
    Route::match(['POST', 'PATCH'], '/tickets/{ticket}/assign-with-ai', [AdminTicketController::class, 'assignWithAI'])->name('tickets.assignWithAI');
});

Route::middleware('auth')->group(function () {
    // Notification Routes
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::get('/notifications/recent', [NotificationController::class, 'recent'])->name('notifications.recent');
    Route::post('/notifications/mark-all-as-read', [NotificationController::class, 'markAllAsRead'])->name('notifications.markAllAsRead');
    Route::delete('/notifications/destroy-all', [NotificationController::class, 'destroyAll'])->name('notifications.destroyAll');
    Route::post('/notifications/mark-as-read', [NotificationController::class, 'markAsRead'])->name('notifications.markAsRead'); // For specific or general marking

    // Chatbot Route
    Route::post('/chatbot/send-message', [ChatbotController::class, 'sendMessage'])->name('chatbot.send');

    // Profile Management Routes
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__ . '/auth.php';
