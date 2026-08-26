<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

class OptimizeSystem extends Command
{
    protected $signature = 'system:optimize';
    protected $description = 'Optimize system for production';

    public function handle()
    {
        $this->info('Optimizing system...');

        // Cache configuration
        $this->info('Caching configuration...');
        Artisan::call('config:cache');

        // Cache routes
        $this->info('Caching routes...');
        Artisan::call('route:cache');

        // Cache views
        $this->info('Caching views...');
        Artisan::call('view:cache');

        // Optimize autoloader
        $this->info('Optimizing autoloader...');
        Artisan::call('optimize');

        // Clear unnecessary cache
        $this->info('Clearing unnecessary cache...');
        Artisan::call('cache:clear');

        // Optimize database
        $this->optimizeDatabase();

        $this->info('System optimization complete!');
        $this->info('Recommendations:');
        $this->info('- Enable Redis for cache');
        $this->info('- Enable gzip compression in Nginx');
        $this->info('- Setup CDN for static assets');
        $this->info('- Configure email queue');
    }

    protected function optimizeDatabase()
    {
        $this->info('Analyzing database tables...');

        $tables = DB::select('SHOW TABLES');
        $tableNames = array_map(function($table) {
            return array_values((array)$table)[0];
        }, $tables);

        foreach ($tableNames as $table) {
            DB::statement("ANALYZE TABLE `{$table}`");
            DB::statement("OPTIMIZE TABLE `{$table}`");
        }

        $this->info('Database optimization complete!');
    }
}