<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\Permission;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class RoleController extends Controller
{
    /**
     * Constructor - require authentication.
     */
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('admin'); // Only admins can manage roles
    }

    /**
     * Display a listing of roles.
     */
    public function index(): View
    {
        $roles = Role::withCount('users')->paginate(15);

        return view('roles.index', compact('roles'));
    }

    /**
     * Show the form for creating a new role.
     */
    public function create(): View
    {
        $permissions = Permission::all();

        return view('roles.create', compact('permissions'));
    }

    /**
     * Store a newly created role in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'unique:roles'],
            'description' => ['nullable', 'string'],
            'permissions' => ['array'],
            'permissions.*' => ['exists:permissions,id'],
        ]);

        try {
            $role = Role::create($request->only('name', 'description'));

            // Attach permissions
            if ($request->has('permissions')) {
                $role->permissions()->attach($request->permissions);
            }

            return redirect()
                ->route('roles.index')
                ->with('success', 'Role created successfully.');
        } catch (\Exception $e) {
            return back()
                ->with('error', 'Failed to create role. Please try again.');
        }
    }

    /**
     * Display the specified role.
     */
    public function show(Role $role): View
    {
        $role->load('permissions', 'users');

        return view('roles.show', compact('role'));
    }

    /**
     * Show the form for editing the specified role.
     */
    public function edit(Role $role): View
    {
        $permissions = Permission::all();
        $role->load('permissions');

        return view('roles.edit', compact('role', 'permissions'));
    }

    /**
     * Update the specified role in storage.
     */
    public function update(Request $request, Role $role): RedirectResponse
    {
        // Prevent modification of super_admin role
        if ($role->name === 'super_admin') {
            return back()
                ->with('error', 'Super Admin role cannot be modified.');
        }

        $request->validate([
            'name' => ['required', 'string', 'unique:roles,name,' . $role->id],
            'description' => ['nullable', 'string'],
            'permissions' => ['array'],
            'permissions.*' => ['exists:permissions,id'],
        ]);

        try {
            $role->update($request->only('name', 'description'));

            // Update permissions
            $role->permissions()->sync($request->permissions ?? []);

            return redirect()
                ->route('roles.show', $role)
                ->with('success', 'Role updated successfully.');
        } catch (\Exception $e) {
            return back()
                ->with('error', 'Failed to update role. Please try again.');
        }
    }

    /**
     * Remove the specified role from storage.
     */
    public function destroy(Role $role): RedirectResponse
    {
        // Prevent deletion of system roles
        if (in_array($role->name, ['super_admin', 'admin'])) {
            return back()
                ->with('error', 'System roles cannot be deleted.');
        }

        // Prevent deletion if role has users
        if ($role->users()->count() > 0) {
            return back()
                ->with('error', 'Cannot delete a role that has users assigned.');
        }

        try {
            $role->delete();

            return redirect()
                ->route('roles.index')
                ->with('success', 'Role deleted successfully.');
        } catch (\Exception $e) {
            return back()
                ->with('error', 'Failed to delete role. Please try again.');
        }
    }
}