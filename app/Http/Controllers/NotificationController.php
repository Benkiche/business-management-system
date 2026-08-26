<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\JsonResponse;

class NotificationController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display notifications center.
     */
    public function index(): View
    {
        $notifications = auth()->user()->notifications()
            ->latest()
            ->paginate(20);

        $unreadCount = auth()->user()->unreadNotificationsCount();

        return view('notifications.index', compact('notifications', 'unreadCount'));
    }

    /**
     * Mark notification as read.
     */
    public function markAsRead(Notification $notification): JsonResponse
    {
        if ($notification->user_id !== auth()->id()) {
            abort(403);
        }

        $notification->markAsRead();

        return response()->json(['success' => true]);
    }

    /**
     * Mark all as read.
     */
    public function markAllAsRead(): JsonResponse
    {
        NotificationService::markAllAsRead(auth()->user());

        return response()->json(['success' => true]);
    }

    /**
     * Get unread notifications (AJAX).
     */
    public function getUnread(): JsonResponse
    {
        $notifications = auth()->user()->notifications()
            ->unread()
            ->latest()
            ->limit(5)
            ->get()
            ->map(function ($notification) {
                return [
                    'id' => $notification->id,
                    'title' => $notification->title,
                    'message' => $notification->message,
                    'type' => $notification->type,
                    'icon' => $notification->icon,
                    'action_url' => $notification->action_url,
                    'created_at' => $notification->created_at->diffForHumans(),
                ];
            });

        return response()->json([
            'count' => auth()->user()->unreadNotificationsCount(),
            'notifications' => $notifications,
        ]);
    }

    /**
     * Delete notification.
     */
    public function delete(Notification $notification): JsonResponse
    {
        if ($notification->user_id !== auth()->id()) {
            abort(403);
        }

        $notification->delete();

        return response()->json(['success' => true]);
    }

    /**
     * Show notification preferences.
     */
    public function preferences(): View
    {
        $user = auth()->user();
        $preferences = $user->getNotificationPreferences();

        return view('notifications.preferences', compact('preferences'));
    }

    /**
     * Update notification preferences.
     */
    public function updatePreferences(Request $request): JsonResponse
    {
        $request->validate([
            'low_stock' => 'boolean',
            'overdue_payment' => 'boolean',
            'payment_received' => 'boolean',
            'sale_created' => 'boolean',
            'expense_approved' => 'boolean',
            'email_notifications' => 'boolean',
        ]);

        auth()->user()->updateNotificationPreferences($request->all());

        return response()->json(['success' => true, 'message' => 'Preferences updated']);
    }
}