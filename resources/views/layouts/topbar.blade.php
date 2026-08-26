@use('Illuminate\Support\Facades\Schema')
<div class="topbar d-flex justify-content-between align-items-center">
    <div class="d-flex align-items-center gap-2">
        <button type="button" class="btn btn-light mobile-menu-toggle" aria-label="Open navigation">
            <i class="fas fa-bars"></i>
        </button>
        <h4 class="mb-0">@yield('page-title', 'Dashboard')</h4>
    </div>
    <!-- In the topbar, add this notification icon -->

<div class="navbar-nav ms-auto">
    <!-- ... existing items ... -->

    <!-- Conversations Quick Access -->
    <div class="nav-item dropdown">
        <a class="nav-link position-relative" href="{{ route('conversations.index') }}" title="Conversations">
            <i class="fas fa-comments fa-lg"></i>
            @php
                $unreadConv = auth()->user()->getUnreadConversationCount();
            @endphp
            @if($unreadConv > 0)
                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                    {{ $unreadConv }}
                </span>
            @endif
        </a>
    </div>
</div>
    <div class="topbar-right">
        <!-- Notifications Bell -->
        <div class="dropdown notification-dropdown">
            <button class="btn btn-link position-relative" type="button" data-bs-toggle="dropdown" id="notificationBell" aria-label="Open notifications">
                <i class="fas fa-bell fa-lg"></i>
                @if(auth()->check() && Schema::hasTable('notifications'))
                    @if(auth()->user()->unreadNotificationsCount() > 0)
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" id="notificationBadge">
                            {{ auth()->user()->unreadNotificationsCount() }}
                        </span>
                    @endif
                @endif
            </button>
            <ul class="dropdown-menu dropdown-menu-end notification-menu">
                <li class="dropdown-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <span>Notifications</span>
                        @if(Schema::hasTable('notifications'))
                            <a href="{{ route('notifications.index') }}" class="small">View all</a>
                        @endif
                    </div>
                </li>
                <li><hr class="dropdown-divider"></li>
                <div id="notificationsList" style="max-height: 400px; overflow-y: auto;">
                    <li class="dropdown-item text-center text-muted py-3">
                        <small>Loading notifications...</small>
                    </li>
                </div>
                <li><hr class="dropdown-divider"></li>
                <li>
                    @if(Schema::hasTable('notifications'))
                        <a class="dropdown-item text-center small" href="{{ route('notifications.index') }}">
                            View all notifications
                        </a>
                    @else
                        <a class="dropdown-item text-center small" href="#" onclick="alert('Notifications system not yet initialized'); return false;">
                            Notifications system initializing...
                        </a>
                    @endif
                </li>
            </ul>
        </div>

        <div class="topbar-user">
            <img 
                src="https://ui-avatars.com/api/?name={{ urlencode(auth()->user()->name) }}&background=667eea&color=fff"
                alt="{{ auth()->user()->name }}" 
                class="rounded-circle"
                width="40"
                height="40"
            >
            <div>
                <small class="d-block text-muted">{{ auth()->user()->role?->name ?? 'N/A' }}</small>
                <strong>{{ auth()->user()->name }}</strong>
            </div>
            
            <div class="dropdown">
                <button class="btn btn-sm btn-light" type="button" id="userDropdown" data-bs-toggle="dropdown">
                    <i class="fas fa-chevron-down"></i>
                </button>
                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                    <li><a class="dropdown-item" href="#">Profile</a></li>
                    <li><a class="dropdown-item" href="#">Settings</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <form method="POST" action="{{ route('logout') }}" class="m-0">
                            @csrf
                            <button type="submit" class="dropdown-item">Logout</button>
                        </form>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>

@section('js')
<script>
let notificationsViewed = false;

// Load notifications on page load (only if table exists)
function loadNotifications() {
    @if(Schema::hasTable('notifications'))
        fetch('{{ route('notifications.unread') }}')
            .then(r => r.json())
            .then(data => {
                const badge = document.getElementById('notificationBadge');
                const unreadCount = notificationsViewed ? 0 : data.count;
                if (unreadCount > 0) {
                    if (!badge) {
                        const newBadge = document.createElement('span');
                        newBadge.id = 'notificationBadge';
                        newBadge.className = 'position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger';
                        newBadge.textContent = data.count;
                        document.getElementById('notificationBell').appendChild(newBadge);
                    } else {
                        badge.textContent = data.count;
                    }
                } else if (badge) {
                    badge.remove();
                }

                const list = document.getElementById('notificationsList');
                if (data.notifications.length > 0) {
                    list.innerHTML = data.notifications.map(n => `
                        <li>
                            <a class="dropdown-item" href="${n.action_url || '#'}">
                                <div class="d-flex">
                                    <i class="${n.icon} me-2 mt-1"></i>
                                    <div class="flex-grow-1">
                                        <small class="fw-bold">${n.title}</small>
                                        <br>
                                        <small class="text-muted">${n.message.substring(0, 50)}...</small>
                                        <br>
                                        <small class="text-muted">${n.created_at}</small>
                                    </div>
                                </div>
                            </a>
                        </li>
                    `).join('');
                } else {
                    list.innerHTML = '<li class="dropdown-item text-center text-muted py-3"><small>No new notifications</small></li>';
                }
            });
    @else
        const list = document.getElementById('notificationsList');
        list.innerHTML = '<li class="dropdown-item text-center text-muted py-3"><small>Notifications system initializing...</small></li>';
    @endif
}

function markNotificationsAsRead() {
    notificationsViewed = true;
    @if(Schema::hasTable('notifications'))
        fetch('{{ route('notifications.mark-all-read') }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
            },
        }).then(() => {
            document.getElementById('notificationBadge')?.remove();
        });
    @endif
}

// Load notifications on page load and refresh every 30 seconds
loadNotifications();
document.getElementById('notificationBell')?.addEventListener('shown.bs.dropdown', markNotificationsAsRead);
document.getElementById('notificationsList')?.addEventListener('click', markNotificationsAsRead);
setInterval(loadNotifications, 30000);
</script>
@endsection
