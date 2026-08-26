<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class ClearCache extends Command
{
    protected $signature = 'cache:purge';
    protected $description = 'Clear all cache types';

    public function handle()
    {
        $this->info('Clearing cache...');

        Artisan::call('cache:clear');
        Artisan::call('config:clear');
        Artisan::call('route:clear');
        Artisan::call('view:clear');
        Artisan::call('optimize:clear');

        $this->info('All cache cleared!');
    }
}