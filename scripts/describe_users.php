<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
use Illuminate\Support\Facades\DB;
$cols = DB::select('SHOW COLUMNS FROM users');
echo json_encode($cols, JSON_PRETTY_PRINT) . "\n";
