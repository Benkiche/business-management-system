<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\Permission;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Super Admin - All permissions
        $superAdmin = Role::where('name', 'super_admin')->first();
        $superAdmin->permissions()->sync(Permission::pluck('id'));

        // Admin
        $admin = Role::where('name', 'admin')->first();
        $adminPermissions = Permission::whereIn('name', [
            'conversation.view',
            'conversation.create',
            'conversation.close',
            'conversation.delete',
            'conversation.manage_participants',
            'message.send',
            'message.edit',
            'message.delete',
        ])->pluck('id');
        $admin->permissions()->sync($adminPermissions);

        // Manager
        $manager = Role::where('name', 'manager')->first();
        $managerPermissions = Permission::whereIn('name', [
            'conversation.view',
            'conversation.create',
            'conversation.close',
            'conversation.manage_participants',
            'message.send',
            'message.edit',
        ])->pluck('id');
        $manager->permissions()->sync($managerPermissions);

        // Salesperson
        $salesperson = Role::where('name', 'salesperson')->first();
        $salespersonPermissions = Permission::whereIn('name', [
            'conversation.view',
            'conversation.create',
            'message.send',
            'message.edit',
        ])->pluck('id');
        $salesperson->permissions()->sync($salespersonPermissions);

        // Storekeeper
        $storekeeper = Role::where('name', 'storekeeper')->first();
        $storekeeperPermissions = Permission::whereIn('name', [
            'conversation.view',
            'message.send',
        ])->pluck('id');
        $storekeeper->permissions()->sync($storekeeperPermissions);

        // Accountant
        $accountant = Role::where('name', 'accountant')->first();
        $accountantPermissions = Permission::whereIn('name', [
            'conversation.view',
            'conversation.create',
            'message.send',
            'message.edit',
        ])->pluck('id');
        $accountant->permissions()->sync($accountantPermissions);
    }
}