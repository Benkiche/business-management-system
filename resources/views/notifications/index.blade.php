@extends('layouts.app')

@section('title', 'Notifications')
@section('page-title', 'Notifications Center')

@section('content')
<div class="row mb-4">
    <div class="col-12 d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Notifications</h5>
        <div class="btn-group">
            <button class="btn btn-sm btn-outline-primary" onclick="markAllAsRead()">
                <i class="fas fa-check-double me-1"></i> Mark All Read
            </button>
            <a href="{{ route('notifications.preferences') }}" class="btn btn-sm btn-outline-secondary">
                <i class="fas fa-cog me-1"></i> Preferences
            </a>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-body p-0">
        @if($notifications->count() > 0)
            <div class="list-group list-group-flush">
                @foreach($notifications as $notification)
                    <div class="list-group-item {{ $notification->isRead() ? '' : 'bg-light' }}">
                        <div class="d-flex justify-content-between align-items-start">
                            <div class="flex-grow-1">
                                <div class="d-flex align-items-center mb-2">
                                    <i class="{{ $notification->icon_class }} me-2"></i>
                                    <h6 class="mb-0">{{ $notification->title }}</h6>
                                    @if(!$notification->isRead())
                                        <span class="badge bg-primary ms-2">New</span>
                                    @endif
                                </div>
                                <p class="mb-1">{{ $notification->message }}</p>
                                <small class="text-muted">{{ $notification->created_at->diffForHumans() }}</small>
                                @if($notification->action_url)
                                    <div class="mt-2">
                                        <a href="{{ $notification->action_url }}" class="btn btn-sm btn-outline-primary">
                                            View Details
                                        </a>
                                    </div>
                                @endif
                            </div>
                            <div class="dropdown ms-2">
                                <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="dropdown">
                                    <i class="fas fa-ellipsis-v"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    @if(!$notification->isRead())
                                        <li>
                                            <a class="dropdown-item" href="#" onclick="markAsRead({{ $notification->id }})">
                                                <i class="fas fa-check me-2"></i> Mark as Read
                                            </a>
                                        </li>
                                    @endif
                                    <li>
                                        <a class="dropdown-item text-danger" href="#" onclick="deleteNotification({{ $notification->id }})">
                                            <i class="fas fa-trash me-2"></i> Delete
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            {{ $notifications->links('pagination::bootstrap-5') }}
        @else
            <div class="text-center py-5">
                <i class="fas fa-bell fa-3x text-muted mb-3"></i>
                <p class="text-muted">No notifications yet</p>
            </div>
        @endif
    </div>
</div>

@section('js')
<script>
function markAsRead(notificationId) {
    fetch(`/notifications/${notificationId}/read`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
        }
    }).then(() => location.reload());
}

function markAllAsRead() {
    fetch('/notifications/mark-all-read', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
        }
    }).then(() => location.reload());
}

function deleteNotification(notificationId) {
    if (confirm('Delete this notification?')) {
        fetch(`/notifications/${notificationId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            }
        }).then(() => location.reload());
    }
}
</script>
@endsection
@endsection