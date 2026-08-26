<?php

// Health check for Docker
$status = [
    'status' => 'healthy',
    'timestamp' => date('Y-m-d H:i:s'),
    'checks' => []
];

try {
    // Check database
    require 'vendor/autoload.php';
    $app = require_once 'bootstrap/app.php';
    $app->make('db')->connection()->getPdo();
    $status['checks']['database'] = 'ok';
} catch (Exception $e) {
    $status['status'] = 'unhealthy';
    $status['checks']['database'] = 'error: ' . $e->getMessage();
}

// Check Redis (optional)
try {
    $redis = new Redis();
    $redis->connect('127.0.0.1', 6379);
    $redis->ping();
    $status['checks']['redis'] = 'ok';
} catch (Exception $e) {
    $status['checks']['redis'] = 'unavailable';
}

// Check storage
if (is_writable('storage')) {
    $status['checks']['storage'] = 'ok';
} else {
    $status['status'] = 'unhealthy';
    $status['checks']['storage'] = 'not writable';
}

header('Content-Type: application/json');
http_response_code($status['status'] === 'healthy' ? 200 : 503);
echo json_encode($status);