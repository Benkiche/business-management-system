<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Define all permissions
        $permissions = [
            // User permissions
            ['name' => 'users.view', 'description' => 'View users list'],
            ['name' => 'users.create', 'description' => 'Create new user'],
            ['name' => 'users.edit', 'description' => 'Edit user details'],
            ['name' => 'users.delete', 'description' => 'Delete user'],

            // Role permissions
            ['name' => 'roles.view', 'description' => 'View roles'],
            ['name' => 'roles.create', 'description' => 'Create role'],
            ['name' => 'roles.edit', 'description' => 'Edit role'],
            ['name' => 'roles.delete', 'description' => 'Delete role'],

            // Customer permissions
            ['name' => 'customers.view', 'description' => 'View customers'],
            ['name' => 'customers.create', 'description' => 'Create customer'],
            ['name' => 'customers.edit', 'description' => 'Edit customer'],
            ['name' => 'customers.delete', 'description' => 'Delete customer'],

            // Product permissions
            ['name' => 'products.view', 'description' => 'View products'],
            ['name' => 'products.create', 'description' => 'Create product'],
            ['name' => 'products.edit', 'description' => 'Edit product'],
            ['name' => 'products.delete', 'description' => 'Delete product'],

            // Sales permissions
            ['name' => 'sales.view', 'description' => 'View sales'],
            ['name' => 'sales.create', 'description' => 'Create sale'],
            ['name' => 'sales.edit', 'description' => 'Edit sale'],
            ['name' => 'sales.delete', 'description' => 'Delete sale'],
            // In the seeding array, add:
['name' => 'sales.view', 'description' => 'View sales'],
['name' => 'sales.create', 'description' => 'Create sales'],
['name' => 'sales.edit', 'description' => 'Edit sales'],
['name' => 'sales.delete', 'description' => 'Delete sales'],
['name' => 'payments.view', 'description' => 'View payments'],
['name' => 'payments.create', 'description' => 'Record payments'],

            // Inventory permissions
            ['name' => 'inventory.view', 'description' => 'View inventory'],
            ['name' => 'inventory.manage', 'description' => 'Manage stock movements'],

            // Payment permissions
            ['name' => 'payments.view', 'description' => 'View payments'],
            ['name' => 'payments.create', 'description' => 'Record payment'],

            // Expense permissions
            ['name' => 'expenses.view', 'description' => 'View expenses'],
            ['name' => 'expenses.create', 'description' => 'Create expense'],
            ['name' => 'expenses.edit', 'description' => 'Edit expense'],
            ['name' => 'expenses.delete', 'description' => 'Delete expense'],

            // Report permissions
            ['name' => 'reports.view', 'description' => 'View reports'],
            ['name' => 'reports.export', 'description' => 'Export reports'],

            // Audit permissions
            ['name' => 'audit.view', 'description' => 'View audit logs'],

            // Add to permissions array:
['name' => 'categories.view', 'description' => 'View categories'],
['name' => 'categories.create', 'description' => 'Create category'],
['name' => 'categories.edit', 'description' => 'Edit category'],
['name' => 'categories.delete', 'description' => 'Delete category'],

['name' => 'suppliers.view', 'description' => 'View suppliers'],
['name' => 'suppliers.create', 'description' => 'Create supplier'],
['name' => 'suppliers.edit', 'description' => 'Edit supplier'],
['name' => 'suppliers.delete', 'description' => 'Delete supplier'],

['name' => 'expenses.view', 'description' => 'View expenses'],
['name' => 'expenses.create', 'description' => 'Create expense'],
['name' => 'expenses.edit', 'description' => 'Edit expense'],
['name' => 'expenses.delete', 'description' => 'Delete expense'],
['name' => 'reports.view', 'description' => 'View financial reports'],
       

    // Add to permissions array:
['name' => 'conversation.view', 'description' => 'View conversations'],
['name' => 'conversation.create', 'description' => 'Create conversations'],
['name' => 'conversation.close', 'description' => 'Close conversations'],
['name' => 'conversation.delete', 'description' => 'Delete conversations'],
['name' => 'conversation.manage_participants', 'description' => 'Manage conversation participants'],
['name' => 'message.send', 'description' => 'Send messages'],
['name' => 'message.edit', 'description' => 'Edit own messages'],
['name' => 'message.delete', 'description' => 'Delete messages'],

 ];


        foreach ($permissions as $permission) {
            Permission::updateOrCreate(['name' => $permission['name']], $permission);
        }

        // Assign permissions to roles
        $this->assignPermissionsToRoles();
    }

    /**
     * Assign permissions to roles.
     */
    private function assignPermissionsToRoles(): void
    {
        $roles = Role::all();

        foreach ($roles as $role) {
            switch ($role->name) {
                case 'super_admin':
                    // Super admin has all permissions
                    $permissions = Permission::all()->pluck('id');
                    $role->permissions()->sync($permissions);
                    break;

                case 'admin':
                    $permissionNames = [
                        'users.view', 'users.create', 'users.edit', 'users.delete',
                        'roles.view', 'roles.create', 'roles.edit',
                        'customers.view', 'customers.create', 'customers.edit', 'customers.delete',
                        'products.view', 'products.create', 'products.edit', 'products.delete',
                        'sales.view',
                        'inventory.view',
                        'reports.view', 'reports.export',
                        'audit.view',
                        'conversation.view', 'conversation.create',
                        'conversation.close', 'conversation.delete',
                        'conversation.manage_participants',
                        'message.send', 'message.edit', 'message.delete',
                    ];
                    $permissions = Permission::whereIn('name', $permissionNames)->pluck('id');
                    $role->permissions()->sync($permissions);
                    break;

                case 'manager':
                    $permissionNames = [
                        'customers.view', 'customers.create', 'customers.edit',
                        'sales.view',
                        'payments.view',
                        'reports.view',
                        'conversation.view', 'conversation.create',
                        'conversation.close', 'conversation.manage_participants',
                        'message.send', 'message.edit',
                    ];
                    $permissions = Permission::whereIn('name', $permissionNames)->pluck('id');
                    $role->permissions()->sync($permissions);
                    break;

                case 'salesperson':
                    $permissionNames = [
                        'customers.view',
                        'products.view',
                        'sales.view', 'sales.create',
                        'payments.view', 'payments.create',
                        'conversation.view', 'conversation.create',
                        'message.send', 'message.edit',
                    ];
                    $permissions = Permission::whereIn('name', $permissionNames)->pluck('id');
                    $role->permissions()->sync($permissions);
                    break;

                case 'storekeeper':
                    $permissionNames = [
                        'products.view',
                        'inventory.view', 'inventory.manage',
                        'conversation.view', 'message.send',
                    ];
                    $permissions = Permission::whereIn('name', $permissionNames)->pluck('id');
                    $role->permissions()->sync($permissions);
                    break;

                case 'accountant':
                    $permissionNames = [
                        'customers.view',
                        'sales.view',
                        'payments.view',
                        'expenses.view', 'expenses.create', 'expenses.edit', 'expenses.delete',
                        'reports.view', 'reports.export',
                        'audit.view',
                        'conversation.view', 'conversation.create',
                        'message.send', 'message.edit',
                    ];
                    $permissions = Permission::whereIn('name', $permissionNames)->pluck('id');
                    $role->permissions()->sync($permissions);
                    break;
            }
        }
    }
}
