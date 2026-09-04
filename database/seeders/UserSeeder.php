<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get roles
        $superAdminRole = Role::where('name', 'super_admin')->first();
        $adminRole = Role::where('name', 'admin')->first();

        // Create super admin user
        User::updateOrCreate(
            ['email' => 'superadmin@business.local'],
            [
                'name' => 'Super Admin',
                'phone' => '+255789123456',
                'password' => Hash::make((string) env('SUPERADMIN_PASSWORD', 'password123')),
                'role_id' => $superAdminRole->id,
                'status' => 'active',
            ]
        );

        // Create admin user
        User::updateOrCreate(
            ['email' => 'admin@business.local'],
            [
                'name' => 'Administrator',
                'phone' => '+255789123457',
                'password' => Hash::make('password123'),
                'role_id' => $adminRole->id,
                'status' => 'active',
            ]
        );

        // Create sample users for other roles
        $roles = Role::whereNotIn('name', ['super_admin', 'admin'])->get();

        foreach ($roles as $role) {
            User::updateOrCreate(
                ['email' => strtolower(str_replace('_', '.', $role->name)) . '@business.local'],
                [
                    'name' => ucwords(str_replace('_', ' ', $role->name)),
                    'phone' => '+255789123' . rand(100, 999),
                    'password' => Hash::make('password123'),
                    'role_id' => $role->id,
                    'status' => 'active',
                ]
            );
        }
    }
}