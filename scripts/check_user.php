<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;

$email = $argv[1] ?? 'superadmin@business.local';
$user = User::where('email', $email)->first();

if (!$user) {
    echo "NOT_FOUND\n";
    exit(0);
}

echo json_encode($user->toArray(), JSON_PRETTY_PRINT) . "\n";
