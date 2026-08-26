<?php

namespace App\Console\Commands;

use App\Services\AuditService;
use App\Services\NotificationService;
use Illuminate\Console\Command;

class PurgeOldLogs extends Command
{
    protected $signature = 'logs:purge';
    protected $description = 'Purge old audit logs and notifications';

    public function handle()
    {
        $this->info('Purging old logs...');

        $auditDeleted = AuditService::purgeOldLogs(90);
        $notificationDeleted = NotificationService::purgeOldNotifications(30);

        $this->info("Deleted $auditDeleted old audit logs (90+ days)");
        $this->info("Deleted $notificationDeleted old notifications (30+ days)");

        $this->info('Purge completed!');
    }
}
