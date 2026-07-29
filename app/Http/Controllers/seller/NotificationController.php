<?php

namespace App\Http\Controllers\seller;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class NotificationController extends Controller
{
    /**
     * Get the notification query based on user role
     */
    private function getNotificationQuery()
    {
        $query = Notification::where(function ($query) {
            $query->whereNull('expires_at')
                  ->orWhere('expires_at', '>', now());
        });

        // If not seller, filter by user_id
        if (Auth::user()->role !== 'seller') {
            $query->where('user_id', Auth::id());
        }

        return $query;
    }

    /**
     * Display a listing of notifications.
     */
    public function index()
    {
        // Handle AJAX request for real-time updates
        if (request('ajax')) {
            $query = Notification::where(function ($query) {
                $query->whereNull('expires_at')
                      ->orWhere('expires_at', '>', now());
            });

            // If authenticated and not seller, filter by user_id
            if (Auth::check() && Auth::user()->role !== 'seller') {
                $query->where('user_id', Auth::id());
            }

            $recentNotifications = $query->latest()
                ->take(5)
                ->get()
                ->map(function ($notification) {
                    $notificationKey = $notification->public_id ?: $notification->id;
                    return [
                        'id' => $notificationKey,
                        'title' => $notification->title,
                        'message' => $notification->message,
                        'read_at' => $notification->read_at,
                        'created_at' => $notification->created_at,
                    ];
                });

            return response()->json(['notifications' => $recentNotifications]);
        }

        // For non-AJAX requests, require authentication
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $notifications = $this->getNotificationQuery()
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        $unreadCount = $this->getNotificationQuery()
            ->unread()
            ->count();

        return view('seller.notifications.index', compact('notifications', 'unreadCount'));
    }

    /**
     * Display the specified notification.
     */
    public function show(string $id)
    {
        $query = Notification::where(function ($query) {
            $query->whereNull('expires_at')
                  ->orWhere('expires_at', '>', now());
        });

        // If not seller, filter by user_id
        if (Auth::user()->role !== 'seller') {
            $query->where('user_id', Auth::id());
        }

        $notification = $query->wherePublicIdOrId($id)->firstOrFail();

        // Mark as read if not already read
        if (!$notification->read_at) {
            $notification->markAsRead();
        }

        return view('seller.notifications.show', compact('notification'));
    }

    /**
     * Mark notification as read.
     */
    public function markAsRead(Request $request, string $id)
    {
        $query = Notification::where(function ($query) {
            $query->whereNull('expires_at')
                  ->orWhere('expires_at', '>', now());
        });

        // If not seller, filter by user_id
        if (Auth::user()->role !== 'seller') {
            $query->where('user_id', Auth::id());
        }

        $notification = $query->wherePublicIdOrId($id)->firstOrFail();
        $notification->markAsRead();

        return response()->json(['success' => true]);
    }

    /**
     * Mark all notifications as read.
     */
    public function markAllAsRead(Request $request)
    {
        $query = Notification::unread()->where(function ($query) {
            $query->whereNull('expires_at')
                  ->orWhere('expires_at', '>', now());
        });

        // If not seller, filter by user_id
        if (Auth::user()->role !== 'seller') {
            $query->where('user_id', Auth::id());
        }

        $query->update(['read_at' => now()]);

        return response()->json(['success' => true]);
    }

    /**
     * Get unread notifications count.
     */
    public function getUnreadCount(Request $request)
    {
        // For now, return a simple count without authentication
        // This will allow the AJAX polling to work
        $count = Notification::where(function ($query) {
            $query->whereNull('expires_at')
                  ->orWhere('expires_at', '>', now());
        })->unread()->count();

        return response()->json(['count' => $count]);
    }

    /**
     * Remove the specified notification.
     */
    public function destroy(string $id)
    {
        $query = Notification::where(function ($query) {
            $query->whereNull('expires_at')
                  ->orWhere('expires_at', '>', now());
        });

        // If not seller, filter by user_id
        if (Auth::user()->role !== 'seller') {
            $query->where('user_id', Auth::id());
        }

        $notification = $query->wherePublicIdOrId($id)->firstOrFail();
        $notification->delete();

        return redirect()->route('seller.notifications.index')
            ->with('success', 'Notification deleted successfully.');
    }
}
