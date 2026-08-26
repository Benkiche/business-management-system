@extends('layouts.app')

@section('title', 'Role: ' . ucfirst(str_replace('_', ' ', $role->name)))
@section('page-title', ucfirst(str_replace('_', ' ', $role->name)))

@section('content')
<div class="row">
    <div class="col-md-4">
        <div class="card">
            <div class="card-body">
                <h6 class="card-title text-muted">Role Details</h6>

                <div class="mb-3">
                    <small class="text-muted">Name</small>
                    <p class="fw-bold">{{ ucfirst(str_replace('_', ' ', $role->name)) }}</p>
                </div>

                <div class="mb-3">
                    <small class="text-muted">Description</small>
                    <p>{{ $role->description ?? 'No description' }}</p>
                </div>

                <div class="mb-3">
                    <small class="text-muted">Users Assigned</small>
                    <p>{{ $role->users->count() }}</p>
                </div>

                @if(auth()->user()->hasPermission('roles.edit') && !in_array($role->name, ['super_admin', 'admin']))
                    <a href="{{ route('roles.edit', $role) }}" class="btn btn-sm btn-warning">
                        <i class="fas fa-edit me-2"></i> Edit
                    </a>
                @endif
            </div>
        </div>
    </div>

    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Assigned Permissions</h5>
            </div>
            <div class="card-body">
                @if($role->permissions->count() > 0)
                    <div class="row">
                        @foreach($role->permissions as $permission)
                            <div class="col-md-6 mb-2">
                                <span class="badge bg-light text-dark border">{{ $permission->name }}</span>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-4">
                        <i class="fas fa-lock fa-2x text-muted mb-2"></i>
                        <p class="text-muted">No permissions assigned.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
