<?php

namespace App\Console\Commands;

use App\Services\AlertService;
use Illuminate\Console\Command;

class CheckSystemAlerts extends Command
{
    protected $signature = 'alerts:check';
    protected $description = 'Check for system alerts (low stock, overdue payments, etc.)';

    public function handle()
    {
        $this->info('Checking system alerts...');

        $results = AlertService::runAllAlerts();

        $this->info("Low stock alerts: {$results['low_stock']}");
        $this->info("Out of stock alerts: {$results['out_of_stock']}");
        $this->info("Overdue payment alerts: {$results['overdue_payments']}");

        $this->info('Alerts check completed!');
    }
}