@extends('layouts.app')

@section('title', 'Conversations')
@section('page-title', 'Conversations & Chat')

@section('content')
<div class="row mb-4">
    <div class="col-lg-9">
        <div>
            <h5 class="mb-1">
                <i class="fas fa-comments me-2"></i>Conversations
            </h5>
            <small class="text-muted">
                <span class="badge bg-info">{{ $stats['total'] }} Total</span>
                <span class="badge bg-success">{{ $stats['open'] }} Open</span>
                <span class="badge bg-warning text-dark">{{ $stats['unread'] }} Unread</span>
            </small>
        </div>
    </div>
    <div class="col-lg-3 text-lg-end">
        @if(auth()->user()->hasPermission('conversation.create'))
            <a href="{{ route('conversations.create') }}" class="btn btn-primary btn-sm">
                <i class="fas fa-plus me-1"></i> New Conversation
            </a>
        @endif
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle me-2"></i>
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-circle me-2"></i>
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="card">
    <div class="card-body">
        <!-- Search & Filters -->
        <form method="GET" action="{{ route('conversations.index') }}" class="mb-4">
            <div class="row g-2">
                <div class="col-md-5">
                    <div class="input-group">
                        <span class="input-group-text">
                            <i class="fas fa-search"></i>
                        </span>
                        <input type="text" name="search" class="form-control" 
                               placeholder="Search by subject..." value="{{ request('search') }}">
                    </div>
                </div>

                <div class="col-md-2">
                    <select name="type" class="form-select">
                        <option value="">All Types</option>
                        <option value="customer_support" {{ request('type') === 'customer_support' ? 'selected' : '' }}>
                            <i class="fas fa-headset"></i> Support
                        </option>
                        <option value="internal" {{ request('type') === 'internal' ? 'selected' : '' }}>
                            <i class="fas fa-users"></i> Internal
                        </option>
                        <option value="sales" {{ request('type') === 'sales' ? 'selected' : '' }}>
                            <i class="fas fa-dollar-sign"></i> Sales
                        </option>
                        <option value="billing" {{ request('type') === 'billing' ? 'selected' : '' }}>
                            <i class="fas fa-receipt"></i> Billing
                        </option>
                    </select>
                </div>

                <div class="col-md-2">
                    <select name="status" class="form-select">
                        <option value="">All Status</option>
                        <option value="open" {{ request('status') === 'open' ? 'selected' : '' }}>Open</option>
                        <option value="closed" {{ request('status') === 'closed' ? 'selected' : '' }}>Closed</option>
                    </select>
                </div>

                <div class="col-md-2">
                    <select name="priority" class="form-select">
                        <option value="">All Priority</option>
                        <option value="3" {{ request('priority') === '3' ? 'selected' : '' }}>🔴 High</option>
                        <option value="2" {{ request('priority') === '2' ? 'selected' : '' }}>🟡 Medium+</option>
                    </select>
                </div>

                <div class="col-md-1">
                    <button type="submit" class="btn btn-outline-primary w-100" title="Filter conversations">
                        <i class="fas fa-filter"></i>
                    </button>
                </div>
            </div>
        </form>

        <!-- Conversations List -->
        @if($conversations->count() > 0)
            <div class="list-group list-group-flush">
                @foreach($conversations as $conversation)
                    @php
                        $unreadCount = $conversation->unreadCount(auth()->user());
                        $lastMessage = $conversation->messages()->latest()->first();
                    @endphp
                    <a href="{{ route('conversations.show', $conversation) }}" 
                       class="list-group-item list-group-item-action py-3 border-bottom {{ $unreadCount > 0 ? 'bg-light fw-bold' : '' }}">
                        
                        <div class="row align-items-center g-0">
                            <!-- Left: Subject & Info -->
                            <div class="col-md-7">
                                <div class="d-flex align-items-start gap-3">
                                    <!-- Status Indicator -->
                                    <div class="pt-1">
                                        @if($conversation->status === 'closed')
                                            <i class="fas fa-lock text-danger"></i>
                                        @else
                                            <i class="fas fa-circle text-{{ $unreadCount > 0 ? 'primary' : 'secondary' }} small"></i>
                                        @endif
                                    </div>

                                    <!-- Content -->
                                    <div class="flex-grow-1 min-width-0">
                                        <div class="d-flex align-items-center gap-2 mb-1">
                                            <h6 class="mb-0 text-truncate">{{ $conversation->subject }}</h6>
                                            
                                            @if($conversation->status === 'closed')
                                                <span class="badge bg-danger flex-shrink-0">Closed</span>
                                            @endif
                                            
                                            @if($unreadCount > 0)
                                                <span class="badge bg-primary flex-shrink-0">{{ $unreadCount }} new</span>
                                            @endif
                                        </div>

                                        <!-- Meta Info -->
                                        <small class="text-muted d-block mb-1">
                                            By <strong>{{ $conversation->creator->name }}</strong>
                                            @if($conversation->customer)
                                                • <strong>{{ $conversation->customer->name }}</strong>
                                            @endif
                                        </small>

                                        <!-- Last Message Preview -->
                                        @if($lastMessage)
                                            <small class="text-muted d-block text-truncate" style="max-width: 100%;">
                                                <em>{{ Str::limit($lastMessage->body, 100) }}</em>
                                            </small>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <!-- Center: Tags & Type -->
                            <div class="col-md-3">
                                <div class="d-flex gap-1 flex-wrap justify-content-center">
                                    <span class="badge bg-light text-dark">
                                        @switch($conversation->type)
                                            @case('customer_support')
                                                <i class="fas fa-headset"></i> Support
                                                @break
                                            @case('internal')
                                                <i class="fas fa-users"></i> Internal
                                                @break
                                            @case('sales')
                                                <i class="fas fa-dollar-sign"></i> Sales
                                                @break
                                            @case('billing')
                                                <i class="fas fa-receipt"></i> Billing
                                                @break
                                        @endswitch
                                    </span>

                                    <span class="badge {{ $conversation->priority === 3 ? 'bg-danger' : ($conversation->priority === 2 ? 'bg-warning text-dark' : 'bg-secondary') }}">
                                        P{{ $conversation->priority }}
                                    </span>
                                </div>
                            </div>

                            <!-- Right: Time & Stats -->
                            <div class="col-md-2 text-end text-muted">
                                <small class="d-block">
                                    <i class="fas fa-clock me-1"></i>
                                    {{ $conversation->last_message_at?->diffForHumans() ?? 'No messages' }}
                                </small>
                                <small class="d-block">
                                    <i class="fas fa-comments me-1"></i>
                                    {{ $conversation->messages->count() }} messages
                                </small>
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>

            <!-- Pagination -->
            <div class="mt-4">
                {{ $conversations->links('pagination::bootstrap-5') }}
            </div>
        @else
            <!-- Empty State -->
            <div class="text-center py-5">
                <div class="mb-3">
                    <i class="fas fa-inbox fa-4x text-muted opacity-50"></i>
                </div>
                <h5 class="text-muted">No Conversations Yet</h5>
                <p class="text-muted mb-3">Start a new conversation to collaborate with your team</p>
                @if(auth()->user()->hasPermission('conversation.create'))
                    <a href="{{ route('conversations.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i> Create First Conversation
                    </a>
                @endif
            </div>
        @endif
    </div>
</div>

@section('js')
<script>
// Auto-refresh unread count every 30 seconds
setInterval(function() {
    fetch('{{ route("conversations.unreadCount") }}')
        .then(res => res.json())
        .then(data => {
            // Update badge if needed
            const badge = document.querySelector('.badge.bg-danger');
            if (badge && data.total_unread_messages > 0) {
                badge.textContent = data.total_unread_messages;
            }
        });
}, 30000);
</script>
@endsection
@endsection