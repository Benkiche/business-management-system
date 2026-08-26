<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Hash;

$email = 'superadmin@business.local';
if (User::where('email', $email)->exists()) {
    echo "User already exists\n";
    exit;
}

$user = User::create([
    'name' => 'Super Admin',
    'email' => $email,
    'password' => Hash::make('password123'),
    'role_id' => 1,
    'status' => 'active',
]);

echo "Created user: " . $user->email . "\n";
