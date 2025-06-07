<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display a listing of all notifications for the user.
     */
    public function index()
    {
        $notifications = Auth::user()->notifications()->paginate(15);
        // Mark all as read when viewing the dedicated page (optional, could be per notification)
        Auth::user()->unreadNotifications->markAsRead();
        return view('notifications.index', compact('notifications'));
    }

    /**
     * Fetch recent (e.g., last 5 unread) notifications for dropdown.
     */
    public function recent()
    {
        $user = Auth::user();
        // Fetch a mix of unread and read, prioritizing unread
        $notifications = $user->notifications()->limit(10)->get(); // Get latest 10 overall
        $unread_count = $user->unreadNotifications->count();

        return response()->json([
            'notifications' => $notifications,
            'unread_count' => $unread_count,
        ]);
    }

    /**
     * Mark all unread notifications as read.
     */
    public function markAllAsRead()
    {
        Auth::user()->unreadNotifications->markAsRead();
        return redirect()->back()->with('success', 'All notifications marked as read.');
    }

    /**
     * Mark a specific notification as read.
     */
    public function markAsRead(Request $request)
    {
        // If using a specific notification ID from the request
        // $notificationId = $request->input('id');
        // if ($notificationId) {
        //     $notification = Auth::user()->notifications()->find($notificationId);
        //     if ($notification) {
        //         $notification->markAsRead();
        //     }
        // } else {
        // Default to marking all as read if no specific ID is provided
        Auth::user()->unreadNotifications->markAsRead();
        // }

        return response()->json(['success' => true]);
    }

    public function destroyAll()
    {
        Auth::user()->notifications()->delete();
        return redirect()->back()->with('success', 'All notifications have been deleted.');
    }
}
