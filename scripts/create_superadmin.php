<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;

$email = 'superadmin@business.local';
$password = env('SUPERADMIN_PASSWORD');

if (!$password) {
    fwrite(STDERR, "SUPERADMIN_PASSWORD is required\n");
    exit(1);
}

$role = Role::firstOrCreate(
    ['name' => 'super_admin'],
    ['description' => 'Full system access. Can manage all modules and users.']
);

$user = User::updateOrCreate(['email' => $email], [
    'name' => 'Super Admin',
    'email' => $email,
    'password' => Hash::make($password),
    'role_id' => $role->id,
    'status' => 'active',
]);

echo "Superadmin credentials updated: " . $user->email . "\n";
