<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\User;
use App\Mail\NotificationMail;
use Illuminate\Support\Facades\Mail;

class NotificationService
{
    /**
     * Create and send notification.
     */
    public static function notify(
        $user,
        string $type,
        string $title,
        string $message,
        string $category,
        ?string $actionUrl = null,
        ?string $icon = null,
        bool $sendEmail = true
    ): ?Notification {
        // Convert to user instance if needed
        if (is_numeric($user)) {
            $user = User::find($user);
        }

        // Check if user has this notification enabled
        if (!$user->isNotificationEnabled($category)) {
            return null;
        }

        // Create notification
        $notification = Notification::create([
            'user_id' => $user->id,
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'category' => $category,
            'action_url' => $actionUrl,
            'icon' => $icon,
            'sent_email' => false,
        ]);

        // Send email if enabled
        if ($sendEmail && $user->getNotificationPreferences()['email_notifications'] ?? true) {
            self::sendEmail($user, $notification);
        }

        return $notification;
    }

    /**
     * Send email notification.
     */
    public static function sendEmail(User $user, Notification $notification): void
    {
        try {
            Mail::to($user->email)->queue(new NotificationMail($notification));
            $notification->update([
                'sent_email' => true,
                'email_sent_at' => now(),
            ]);
        } catch (\Exception $e) {
            \Log::error('Failed to send notification email: ' . $e->getMessage());
        }
    }

    /**
     * Get user's recent notifications.
     */
    public static function getUserNotifications($user, $limit = 20)
    {
        return Notification::where('user_id', $user->id)
            ->latest()
            ->limit($limit)
            ->get();
    }

    /**
     * Mark all as read.
     */
    public static function markAllAsRead($user): void
    {
        Notification::where('user_id', $user->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        $user->update(['last_notification_read_at' => now()]);
    }

    /**
     * Delete old notifications.
     */
    public static function purgeOldNotifications($daysOld = 30)
    {
        return Notification::where('created_at', '<', now()->subDays($daysOld))->delete();
    }

    /**
     * Notify multiple users.
     */
    public static function notifyMultiple(
        array $userIds,
        string $type,
        string $title,
        string $message,
        string $category,
        ?string $actionUrl = null,
        bool $sendEmail = true
    ): int {
        $count = 0;
        foreach ($userIds as $userId) {
            if (self::notify($userId, $type, $title, $message, $category, $actionUrl, null, $sendEmail)) {
                $count++;
            }
        }
        return $count;
    }

    /**
     * Notify admins.
     */
    public static function notifyAdmins(
        string $type,
        string $title,
        string $message,
        string $category,
        ?string $actionUrl = null
    ): int {
        $admins = User::whereHas('role', function ($q) {
            $q->where('name', 'admin');
        })->pluck('id');

        return self::notifyMultiple($admins->toArray(), $type, $title, $message, $category, $actionUrl);
    }
}