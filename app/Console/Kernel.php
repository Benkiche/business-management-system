<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected function schedule(Schedule $schedule)
    {
        // Check system alerts daily at 8 AM
        $schedule->command('alerts:check')
            ->dailyAt('08:00')
            ->name('system-alerts-check')
            ->withoutOverlapping()
            ->onOneServer();

        // Purge old logs weekly
        $schedule->command('logs:purge')
            ->weekly()
            ->name('purge-old-logs')
            ->withoutOverlapping();

        // Optimize database monthly
        $schedule->command('db:optimize')
            ->monthlyOn(1, '03:00')
            ->name('database-optimization')
            ->withoutOverlapping();

        // Backup database daily
        $schedule->exec('mysqldump business_management > /backups/db-' . date('Y-m-d') . '.sql')
            ->dailyAt('02:00')
            ->name('database-backup')
            ->withoutOverlapping();

        // Send daily summary emails
        $schedule->command('email:daily-summary')
            ->dailyAt('09:00')
            ->name('daily-summary-emails')
            ->withoutOverlapping();

        // Cache cleanup
        $schedule->command('cache:prune-stale-tags')
            ->hourly()
            ->name('cache-cleanup')
            ->withoutOverlapping();
    }

    protected function commands()
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}