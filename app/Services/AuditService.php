<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Support\Facades\Request;

class AuditService
{
    /**
     * Log an audit entry.
     */
    public static function log(
        string $action,
        string $entityType,
        $entityId = null,
        ?string $description = null,
        ?array $oldValues = null,
        ?array $newValues = null,
        string $status = 'success',
        ?string $errorMessage = null
    ): AuditLog {
        return AuditLog::create([
            'user_id' => auth()->id(),
            'action' => $action,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'description' => $description,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
            'status' => $status,
            'error_message' => $errorMessage,
            'action_at' => now(),
        ]);
    }

    /**
     * Get audit trail for entity.
     */
    public static function getEntityAuditTrail($entityType, $entityId)
    {
        return AuditLog::forEntity($entityType, $entityId)
            ->with('user')
            ->latest('action_at')
            ->get();
    }

    /**
     * Get user activity report.
     */
    public static function getUserActivity($userId, $fromDate, $toDate)
    {
        return AuditLog::where('user_id', $userId)
            ->dateRange($fromDate, $toDate)
            ->with('user')
            ->latest('action_at')
            ->get();
    }

    /**
     * Get entity activity summary.
     */
    public static function getEntityActivitySummary($fromDate, $toDate)
    {
        $logs = AuditLog::dateRange($fromDate, $toDate)->get();

        return [
            'total_actions' => $logs->count(),
            'by_action' => $logs->groupBy('action')->map(fn($items) => $items->count())->toArray(),
            'by_entity' => $logs->groupBy('entity_type')->map(fn($items) => $items->count())->toArray(),
            'failed_actions' => $logs->where('status', 'failed')->count(),
            'by_user' => $logs->groupBy('user_id')->map(fn($items) => [
                'count' => $items->count(),
                'user' => $items->first()->user->name ?? 'Unknown',
            ])->toArray(),
        ];
    }

    /**
     * Get suspicious activity.
     */
    public static function getSuspiciousActivity()
    {
        // Get failed actions
        $failed = AuditLog::where('status', 'failed')
            ->latest('action_at')
            ->limit(20)
            ->get();

        // Get multiple failed attempts by user
        $multipleFailed = AuditLog::where('status', 'failed')
            ->where('action_at', '>=', now()->subHour())
            ->get()
            ->groupBy('user_id')
            ->filter(fn($items) => $items->count() >= 3);

        return [
            'failed_actions' => $failed,
            'suspicious_users' => $multipleFailed,
        ];
    }

    /**
     * Purge old audit logs.
     */
    public static function purgeOldLogs($daysOld = 90)
    {
        return AuditLog::where('action_at', '<', now()->subDays($daysOld))->delete();
    }
}