@extends('layouts.app')

@section('title', $conversation->subject)
@section('page-title', $conversation->subject)

@section('css')
<style>
    .conversation-page .chat-card {
        height: min(680px, calc(100vh - 170px));
        min-height: 480px;
        position: sticky;
        top: 90px;
    }

    .conversation-page .messages-container {
        background: #f5f7fb;
        background-attachment: fixed;
        padding: 1.25rem 1.5rem;
        min-height: 0;
        overflow-y: auto;
        overscroll-behavior: contain;
    }

    .conversation-page .message-content {
        max-width: min(78%, 640px);
    }

    .conversation-page .message-bubble {
        font-size: 0.95rem;
        line-height: 1.45;
        overflow-wrap: anywhere;
    }

    .conversation-page .composer {
        padding: 0.65rem 1rem;
    }

    .conversation-page .composer-row {
        display: flex;
        align-items: flex-end;
        gap: 0.6rem;
    }

    .conversation-page .composer textarea {
        height: 44px;
        min-height: 44px;
        max-height: 110px;
        resize: vertical;
        font-size: 0.9rem;
        line-height: 1.4;
    }

    .conversation-page .composer .btn {
        min-height: 44px;
        padding: 0.5rem 0.9rem;
        font-size: 0.9rem;
        white-space: nowrap;
    }

    @media (max-width: 767.98px) {
        .conversation-page .chat-card {
            height: auto;
            min-height: 0;
            position: static;
        }

        .conversation-page .messages-container {
            min-height: 360px;
            max-height: 60vh;
            padding: 1rem;
        }

        .conversation-page .message-content {
            max-width: 88%;
        }

        .conversation-page .composer-row {
            align-items: stretch;
        }

        .conversation-page .composer .btn {
            padding-inline: 0.85rem;
        }
    }
</style>
@endsection

@section('content')
<div class="conversation-page row h-100">
    <!-- Main Chat Area -->
    <div class="col-lg-8">
        <div class="card chat-card h-100 d-flex flex-column">
            
            <!-- Header -->
            <div class="card-header bg-light border-bottom d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="mb-0">
                        <i class="fas fa-comments me-2"></i>{{ $conversation->subject }}
                    </h5>
                    <small class="text-muted">
                        <i class="fas fa-comments me-1"></i>{{ $conversation->messages->count() }} messages
                        @if($conversation->status === 'closed')
                            <span class="badge bg-danger ms-2">Closed</span>
                        @else
                            <span class="badge bg-success ms-2">Open</span>
                        @endif
                    </small>
                </div>

                <div class="btn-group btn-group-sm">
                    @if(auth()->user()->isAdmin() || $conversation->creator->is(auth()->user()))
                        @if($conversation->status === 'open')
                            <form method="POST" action="{{ route('conversations.close', $conversation) }}" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-outline-danger" title="Close conversation">
                                    <i class="fas fa-lock me-1"></i> Close
                                </button>
                            </form>
                        @else
                            <form method="POST" action="{{ route('conversations.reopen', $conversation) }}" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-outline-success" title="Reopen conversation">
                                    <i class="fas fa-unlock me-1"></i> Reopen
                                </button>
                            </form>
                        @endif
                    @endif

                    <a href="{{ route('conversations.index') }}" class="btn btn-outline-secondary" title="Back to conversations">
                        <i class="fas fa-arrow-left"></i>
                    </a>
                </div>
            </div>

            <!-- Messages Area -->
            <div class="card-body messages-container flex-grow-1 overflow-y-auto" id="messagesContainer">
                @if($conversation->messages->count() > 0)
                    @foreach($conversation->messages as $message)
                        @if($message->type === 'system')
                            <!-- System Message -->
                            <div class="text-center my-3">
                                <small class="text-muted bg-white px-3 py-1 rounded-pill d-inline-block">
                                    <i class="fas fa-info-circle me-1"></i>{{ $message->body }}
                                </small>
                            </div>
                        @else
                            <!-- Regular Message -->
                            <div class="mb-3 d-flex {{ auth()->user()->is($message->sender) ? 'justify-content-end' : '' }}">
                                <div class="message-content">
                                    <!-- Sender Info -->
                                    <small class="text-muted d-block {{ auth()->user()->is($message->sender) ? 'text-end' : '' }} mb-1">
                                        <strong>{{ $message->sender_name }}</strong>
                                        <span class="ms-2">{{ $message->created_at->format('H:i') }}</span>
                                    </small>

                                    <!-- Message Bubble -->
                                    <div class="message-bubble p-3 rounded border {{ auth()->user()->is($message->sender) ? 'bg-primary text-white border-primary' : 'bg-white' }}">
                                        <p class="mb-2">{{ $message->body }}</p>

                                        @if($message->is_edited)
                                            <small class="text-muted d-block {{ auth()->user()->is($message->sender) ? 'text-white-50' : '' }}">
                                                (edited {{ $message->edited_at->format('H:i') }})
                                            </small>
                                        @endif

                                        <!-- Actions (Edit/Delete) -->
                                        @if(auth()->user()->is($message->sender) || auth()->user()->isAdmin())
                                            <div class="mt-2">
                                                <button class="btn btn-sm btn-link p-0 {{ auth()->user()->is($message->sender) ? 'text-white' : 'text-muted' }}" 
                                                        onclick="editMessage({{ $message->id }})">
                                                    <i class="fas fa-edit me-1"></i>Edit
                                                </button>
                                                <button class="btn btn-sm btn-link p-0 text-danger ms-2" 
                                                        onclick="deleteMessage({{ $message->id }})">
                                                    <i class="fas fa-trash me-1"></i>Delete
                                                </button>
                                            </div>
                                        @endif
                                    </div>

                                    <!-- Read Receipts -->
                                    @if($message->reads->count() > 0 && auth()->user()->is($message->sender))
                                        <small class="text-muted d-block mt-1 text-end">
                                            ✓ Read by {{ $message->reads->count() }}
                                        </small>
                                    @endif
                                </div>
                            </div>
                        @endif
                    @endforeach
                @else
                    <!-- No Messages Yet -->
                    <div class="text-center py-5">
                        <i class="fas fa-comments fa-4x text-muted opacity-25 mb-3"></i>
                        <p class="text-muted">No messages yet. Start the conversation!</p>
                    </div>
                @endif
            </div>

            <!-- Message Input Form -->
            @if($conversation->status === 'open')
                <div class="card-footer composer bg-light border-top">
                    <form id="messageForm" method="POST" action="{{ route('messages.store', $conversation) }}">
                        @csrf
                        <div class="composer-row">
                            <textarea name="body" class="form-control" id="messageInput" 
                                      placeholder="Type your message... (Press Shift+Enter for new line)" 
                                      rows="2" required></textarea>
                            <button type="submit" class="btn btn-primary" title="Send message (Ctrl+Enter)">
                                <i class="fas fa-paper-plane me-1"></i> Send
                            </button>
                        </div>
                    </form>
                </div>
            @else
                <div class="card-footer bg-light text-center text-muted border-top">
                    <small>
                        <i class="fas fa-lock me-1"></i>This conversation is closed. 
                        @if(auth()->user()->isAdmin())
                            <form method="POST" action="{{ route('conversations.reopen', $conversation) }}" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-link p-0">Reopen it</button>
                            </form>
                        @endif
                    </small>
                </div>
            @endif
        </div>
    </div>

    <!-- Right Sidebar -->
    <div class="col-lg-4">
        <!-- Conversation Info Card -->
        <div class="card mb-3">
            <div class="card-header bg-light">
                <h6 class="mb-0">
                    <i class="fas fa-info-circle me-2"></i>Conversation Info
                </h6>
            </div>
            <div class="card-body small">
                <div class="mb-3">
                    <small class="text-muted d-block">Created by</small>
                    <strong class="d-flex align-items-center gap-2">
                        <i class="fas fa-user-circle"></i>{{ $conversation->creator->name }}
                    </strong>
                </div>

                <div class="mb-3">
                    <small class="text-muted d-block">Type</small>
                    <span class="badge bg-info text-dark">{{ ucfirst(str_replace('_', ' ', $conversation->type)) }}</span>
                </div>

                <div class="mb-3">
                    <small class="text-muted d-block">Priority</small>
                    <span class="badge {{ $conversation->priority === 3 ? 'bg-danger' : ($conversation->priority === 2 ? 'bg-warning text-dark' : 'bg-secondary') }}">
                        Level {{ $conversation->priority }}
                    </span>
                </div>

                @if($conversation->customer)
                    <div class="mb-3">
                        <small class="text-muted d-block">Customer</small>
                        <a href="{{ route('customers.show', $conversation->customer) }}" class="text-decoration-none">
                            <i class="fas fa-building me-1"></i>{{ $conversation->customer->name }}
                        </a>
                    </div>
                @endif

                <div>
                    <small class="text-muted d-block">Started</small>
                    <small>{{ $conversation->created_at->format('M d, Y H:i') }}</small>
                </div>
            </div>
        </div>

        <!-- Participants Card -->
        <div class="card">
            <div class="card-header bg-light d-flex justify-content-between align-items-center">
                <h6 class="mb-0">
                    <i class="fas fa-users me-2"></i>Participants ({{ $conversation->participants->count() }})
                </h6>
                @if(auth()->user()->isAdmin() || $conversation->creator->is(auth()->user()))
                    <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#addParticipantModal" title="Add participant">
                        <i class="fas fa-plus"></i>
                    </button>
                @endif
            </div>
            <div class="card-body p-0">
                <div class="list-group list-group-flush">
                    @forelse($conversation->participants as $participant)
                        <div class="list-group-item py-2 px-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="flex-grow-1">
                                    <strong class="d-block">
                                        <i class="fas fa-user-circle me-1"></i>{{ $participant->name }}
                                    </strong>
                                    <small class="text-muted d-block">
                                        @if($participant->is($conversation->creator))
                                            <span class="badge bg-primary">Creator</span>
                                        @else
                                            {{ ucfirst($participant->role->name ?? 'Member') }}
                                        @endif
                                    </small>
                                </div>

                                <!-- Remove Button -->
                                @if((auth()->user()->isAdmin() || $conversation->creator->is(auth()->user())) && !$participant->is($conversation->creator))
                                    <form method="POST" action="{{ route('conversations.removeParticipant', [$conversation, $participant]) }}" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger" 
                                                title="Remove participant"
                                                onclick="return confirm('Remove this participant?')">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="list-group-item text-muted text-center py-3">
                            <small>No participants</small>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Participant Modal -->
@if(auth()->user()->isAdmin() || $conversation->creator->is(auth()->user()))
    <div class="modal fade" id="addParticipantModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add Participant</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="{{ route('conversations.addParticipant', $conversation) }}">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Select User</label>
                            <select name="user_id" class="form-select" required>
                                <option value="">-- Select User --</option>
                                @php
                                    $existingIds = $conversation->participants->pluck('id')->toArray();
                                @endphp
                                @foreach(\App\Models\User::where('status', 'active')->where('id', '!=', auth()->id())->orderBy('name')->get() as $user)
                                    @if(!in_array($user->id, $existingIds))
                                        <option value="{{ $user->id }}">
                                            {{ $user->name }} ({{ $user->role->name ?? 'User' }})
                                        </option>
                                    @endif
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Add Participant</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endif

@section('js')
<script>
// Auto-scroll to bottom
const container = document.getElementById('messagesContainer');
const messageInput = document.getElementById('messageInput');
function scrollToBottom() {
    if (container) {
        container.scrollTop = container.scrollHeight;
    }
}
scrollToBottom();

messageInput?.addEventListener('keydown', function(e) {
    if (e.ctrlKey && e.key === 'Enter') {
        e.preventDefault();
        document.getElementById('messageForm')?.requestSubmit();
    }
});

// Send message via AJAX
document.getElementById('messageForm')?.addEventListener('submit', function(e) {
    e.preventDefault();
    
    const body = document.getElementById('messageInput').value.trim();
    if (!body) return;
    
    const formData = new FormData();
    formData.append('body', body);
    
    fetch('{{ route("messages.store", $conversation) }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ body })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            document.getElementById('messageInput').value = '';
            location.reload();
        } else {
            alert('Error: ' + (data.error || 'Failed to send message'));
        }
    })
    .catch(err => {
        console.error('Error:', err);
        alert('Failed to send message');
    });
});

// Edit message
function editMessage(id) {
    const newBody = prompt('Edit your message:');
    if (newBody && newBody.trim()) {
        fetch(`/messages/${id}`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ body: newBody })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                location.reload();
            }
        });
    }
}

// Delete message
function deleteMessage(id) {
    if (confirm('Delete this message permanently?')) {
        fetch(`/messages/${id}`, {
            method: 'DELETE',
            headers: {'X-CSRF-TOKEN': '{{ csrf_token() }}'}
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                location.reload();
            }
        });
    }
}

// Auto-refresh every 10 seconds
setInterval(function() {
    fetch('{{ route("conversations.show", $conversation) }}')
        .then(res => res.text())
        .then(html => {
            // Check if new messages
            const newCount = html.match(/(\d+) messages/)?.[1];
            const oldCount = '{{ $conversation->messages->count() }}';
            if (newCount && newCount > oldCount) {
                location.reload();
            }
        });
}, 10000);
</script>
@endsection
@endsection