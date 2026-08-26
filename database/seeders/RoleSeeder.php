<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            [
                'name' => 'super_admin',
                'description' => 'Full system access. Can manage all modules and users.',
            ],
            [
                'name' => 'admin',
                'description' => 'Administrative access. Can manage users, products, and view reports.',
            ],
            [
                'name' => 'manager',
                'description' => 'Can view reports and manage customers.',
            ],
            [
                'name' => 'salesperson',
                'description' => 'Can create and manage sales.',
            ],
            [
                'name' => 'storekeeper',
                'description' => 'Can manage inventory and stock movements.',
            ],
            [
                'name' => 'accountant',
                'description' => 'Can view financial reports and manage expenses.',
            ],
        ];

        foreach ($roles as $role) {
            Role::updateOrCreate(['name' => $role['name']], $role);
        }
    }
}