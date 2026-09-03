@extends('layouts.app')

@section('title', $conversation->subject)
@section('page-title', $conversation->subject)

@section('css')
<style>
    body { overflow-x: hidden; }

    .conversation-page {
        display: flex;
        height: 100%;
        gap: 1.5rem;
    }

    .chat-container {
        flex: 1;
        display: flex;
        flex-direction: column;
        min-height: 0;
        min-width: 0;
    }

    .chat-container .card {
        display: flex;
        flex-direction: column;
        height: 100%;
        overflow: hidden;
        border-radius: 8px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
    }

    .chat-container .card-header {
        flex-shrink: 0;
        padding: 1rem 1.25rem;
        background: #fff;
        border-bottom: 1px solid #e9ecef;
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 1rem;
    }

    .chat-container .card-header h5 {
        margin: 0;
        font-size: 1.15rem;
        font-weight: 600;
        color: #212529;
    }

    .messages-container {
        flex: 1;
        overflow-y: auto;
        overflow-x: hidden;
        background: #f8f9fb;
        padding: 1.25rem 1.5rem;
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }

    .message-group { display: flex; margin-bottom: 0.5rem; }
    .message-group.sent { justify-content: flex-end; }
    .message-group.received { justify-content: flex-start; }

    .message-content { max-width: min(75%, 550px); }

    .message-bubble {
        border-radius: 12px;
        padding: 0.75rem 1rem;
        font-size: 0.95rem;
        line-height: 1.5;
        word-wrap: break-word;
    }

    .message-group.sent .message-bubble {
        background: #0d6efd;
        color: white;
        border-bottom-right-radius: 4px;
    }

    .message-group.received .message-bubble {
        background: white;
        color: #212529;
        border: 1px solid #e9ecef;
        border-bottom-left-radius: 4px;
    }

    .message-meta {
        font-size: 0.8rem;
        margin-top: 0.25rem;
        display: flex;
        gap: 0.5rem;
    }

    .message-group.sent .message-meta { color: rgba(255,255,255,0.7); justify-content: flex-end; }
    .message-group.received .message-meta { color: #6c757d; justify-content: flex-start; }

    .empty-state {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        text-align: center;
        min-height: 200px;
        color: #adb5bd;
    }

    .empty-state i { font-size: 3rem; margin-bottom: 1rem; opacity: 0.3; }

    .composer-section {
        flex-shrink: 0;
        background: #fff;
        border-top: 1px solid #e9ecef;
        padding: 0.75rem 1rem;
        max-height: 110px;
        overflow: hidden;
    }

    .composer-section form { margin: 0; padding: 0; }

    .composer-inputs {
        display: flex;
        gap: 0.75rem;
        align-items: flex-end;
        height: 44px;
        overflow: hidden;
    }

    .composer-textarea-wrapper {
        flex: 1;
        display: flex;
        flex-direction: column;
        height: 100%;
        min-width: 0;
        overflow: hidden;
    }

    .composer-textarea {
        flex: 1;
        resize: none;
        overflow-y: auto;
        border: 1px solid #dee2e6;
        border-radius: 6px;
        padding: 0.6rem 0.75rem;
        font-size: 0.9rem;
        font-family: inherit;
        min-height: 44px;
        max-height: 44px;
        line-height: 1.4;
        transition: border-color 0.2s, box-shadow 0.2s;
    }

    .composer-textarea:focus {
        outline: none;
        border-color: #0d6efd;
        box-shadow: 0 0 0 3px rgba(13, 110, 253, 0.1);
    }

    .composer-btn {
        flex-shrink: 0;
        padding: 0.6rem 1rem;
        background: #0d6efd;
        color: white;
        border: none;
        border-radius: 6px;
        font-size: 0.9rem;
        font-weight: 500;
        cursor: pointer;
        white-space: nowrap;
        height: 44px;
        transition: background 0.2s;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .composer-btn:hover:not(:disabled) { background: #0b5ed7; }
    .composer-btn:disabled { opacity: 0.6; cursor: not-allowed; }

    @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
    .composer-btn .icon { animation: spin 0.6s linear infinite; }

    .sidebar-container {
        width: 320px;
        display: flex;
        flex-direction: column;
        gap: 1rem;
        overflow-y: auto;
        padding-right: 0.5rem;
    }

    .sidebar-container .card {
        border-radius: 8px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        flex-shrink: 0;
    }

    @media (max-width: 1199px) {
        .conversation-page { flex-direction: column; gap: 1rem; }
        .sidebar-container { width: 100%; flex-direction: row; max-height: 200px; overflow-x: auto; overflow-y: hidden; }
        .sidebar-container .card { min-width: 280px; flex-shrink: 0; }
        .chat-container { min-height: 500px; }
    }

    @media (max-width: 767px) {
        .conversation-page { gap: 0.75rem; }
        .message-content { max-width: 85%; }
        .messages-container { padding: 1rem; }
        .sidebar-container { max-height: 150px; }
        .composer-textarea { font-size: 16px; }
    }
</style>
@endsection

@section('content')
<div class="conversation-page">
    <!-- Main Chat Area -->
    <div class="chat-container">
        <div class="card">
            <!-- Header -->
            <div class="card-header">
                <div>
                    <h5>
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

                <div class="btn-group btn-group-sm flex-shrink-0">
                    @if(auth()->user()->isAdmin() || $conversation->creator->is(auth()->user()))
                        @if($conversation->status === 'open')
                            <form method="POST" action="{{ route('conversations.close', $conversation) }}" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-outline-danger btn-sm" title="Close conversation">
                                    <i class="fas fa-lock me-1"></i> Close
                                </button>
                            </form>
                        @else
                            <form method="POST" action="{{ route('conversations.reopen', $conversation) }}" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-outline-success btn-sm" title="Reopen conversation">
                                    <i class="fas fa-unlock me-1"></i> Reopen
                                </button>
                            </form>
                        @endif
                    @endif

                    <a href="{{ route('conversations.index') }}" class="btn btn-outline-secondary btn-sm" title="Back">
                        <i class="fas fa-arrow-left"></i>
                    </a>
                </div>
            </div>

            <!-- Messages Area -->
            <div class="messages-container" id="messagesContainer">
                @if($conversation->messages->count() > 0)
                    @foreach($conversation->messages as $message)
                        @if($message->type === 'system')
                            <div class="text-center">
                                <small class="text-muted bg-white px-3 py-1 rounded-pill d-inline-block border">
                                    <i class="fas fa-info-circle me-1"></i>{{ $message->body }}
                                </small>
                            </div>
                        @else
                            <div class="message-group {{ auth()->user()->is($message->sender) ? 'sent' : 'received' }}">
                                <div class="message-content">
                                    <small class="message-meta">
                                        <strong>{{ $message->sender_name }}</strong>
                                        <span>{{ $message->created_at->format('H:i') }}</span>
                                        @if($message->is_edited)
                                            <span class="text-muted">(edited)</span>
                                        @endif
                                    </small>
                                    <div class="message-bubble">
                                        <p class="mb-0">{{ $message->body }}</p>
                                        @if($message->reads->count() > 0 && auth()->user()->is($message->sender))
                                            <small class="mt-1 d-block" style="opacity: 0.7;">
                                                ✓ Read by {{ $message->reads->count() }}
                                            </small>
                                        @endif
                                    </div>
                                    @if(auth()->user()->is($message->sender) || auth()->user()->isAdmin())
                                        <small class="message-meta mt-1">
                                            <button class="btn btn-link btn-sm p-0 text-muted" onclick="editMessage({{ $message->id }})">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="btn btn-link btn-sm p-0 text-danger" onclick="deleteMessage({{ $message->id }})">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </small>
                                    @endif
                                </div>
                            </div>
                        @endif
                    @endforeach
                @else
                    <div class="empty-state">
                        <i class="fas fa-comments"></i>
                        <p>No messages yet. Start the conversation!</p>
                    </div>
                @endif
            </div>

            <!-- Composer Section -->
            @if($conversation->status === 'open')
                <div class="composer-section">
                    <form id="messageForm" method="POST" action="{{ route('messages.store', $conversation) }}">
                        @csrf
                        <div class="composer-inputs">
                            <div class="composer-textarea-wrapper">
                                <textarea 
                                    name="body" 
                                    class="composer-textarea" 
                                    id="messageInput"
                                    placeholder="Type your message... (Ctrl+Enter to send)"
                                    required
                                    maxlength="5000"></textarea>
                            </div>
                            <button type="submit" class="composer-btn" id="sendBtn" title="Send message (Ctrl+Enter)">
                                <i class="fas fa-paper-plane"></i>
                                <span>Send</span>
                            </button>
                        </div>
                    </form>
                </div>
            @else
                <div class="composer-section text-center text-muted">
                    <small>
                        <i class="fas fa-lock me-1"></i>This conversation is closed.
                        @if(auth()->user()->isAdmin())
                            <form method="POST" action="{{ route('conversations.reopen', $conversation) }}" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-link btn-sm p-0">Reopen it</button>
                            </form>
                        @endif
                    </small>
                </div>
            @endif
        </div>
    </div>

    <!-- Right Sidebar -->
    <div class="sidebar-container">
        <!-- Conversation Info -->
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0">
                    <i class="fas fa-info-circle me-2"></i>Info
                </h6>
            </div>
            <div class="card-body small">
                <div class="mb-2">
                    <small class="text-muted d-block">Created by</small>
                    <strong>{{ $conversation->creator->name }}</strong>
                </div>
                <div class="mb-2">
                    <small class="text-muted d-block">Type</small>
                    <span class="badge bg-info text-dark">{{ ucfirst(str_replace('_', ' ', $conversation->type)) }}</span>
                </div>
                <div class="mb-2">
                    <small class="text-muted d-block">Priority</small>
                    <span class="badge {{ $conversation->priority === 3 ? 'bg-danger' : ($conversation->priority === 2 ? 'bg-warning text-dark' : 'bg-secondary') }}">
                        Level {{ $conversation->priority }}
                    </span>
                </div>
                @if($conversation->customer)
                    <div>
                        <small class="text-muted d-block">Customer</small>
                        <a href="{{ route('customers.show', $conversation->customer) }}" class="text-decoration-none">
                            {{ $conversation->customer->name }}
                        </a>
                    </div>
                @endif
            </div>
        </div>

        <!-- Participants -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="mb-0">
                    <i class="fas fa-users me-2"></i>Participants
                </h6>
                @if(auth()->user()->isAdmin() || $conversation->creator->is(auth()->user()))
                    <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#addParticipantModal" title="Add participant">
                        <i class="fas fa-plus"></i>
                    </button>
                @endif
            </div>
            <div class="card-body p-2">
                <div class="list-group list-group-sm">
                    @forelse($conversation->participants as $participant)
                        <div class="list-group-item border-0 p-2">
                            <small>
                                <strong>{{ $participant->name }}</strong><br>
                                @if($participant->is($conversation->creator))
                                    <span class="badge bg-primary">Creator</span>
                                @else
                                    <span class="badge bg-secondary">{{ ucfirst($participant->role->name ?? 'Member') }}</span>
                                @endif
                                @if((auth()->user()->isAdmin() || $conversation->creator->is(auth()->user())) && !$participant->is($conversation->creator))
                                    <form method="POST" action="{{ route('conversations.removeParticipant', [$conversation, $participant]) }}" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-link btn-sm p-0 text-danger ms-2" title="Remove" onclick="return confirm('Remove?')">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </form>
                                @endif
                            </small>
                        </div>
                    @empty
                        <div class="p-2 text-muted text-center">
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
                        <label class="form-label">Select User</label>
                        <select name="user_id" class="form-select" required>
                            <option value="">-- Select User --</option>
                            @php $existingIds = $conversation->participants->pluck('id')->toArray(); @endphp
                            @foreach(\App\Models\User::where('status', 'active')->where('id', '!=', auth()->id())->orderBy('name')->get() as $user)
                                @if(!in_array($user->id, $existingIds))
                                    <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->role->name ?? 'User' }})</option>
                                @endif
                            @endforeach
                        </select>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Add</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endif
@endsection

@section('js')
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
                            <div class="composer-wrapper">
                                <textarea name="body" class="form-control" id="messageInput" 
                                          placeholder="Type your message... (Ctrl+Enter to send)" 
                                          rows="2" required maxlength="5000"></textarea>
                                <div class="composer-actions">
                                    <div class="char-count">
                                        <span id="charCount">0</span>/5000
                                    </div>
                                    <div class="quick-actions">
                                        <button type="button" class="btn-icon" title="Emoji" onclick="return false;" disabled>
                                            <i class="fas fa-smile"></i>
                                        </button>
                                        <button type="button" class="btn-icon" title="Keyboard shortcuts" data-bs-toggle="tooltip" onclick="showShortcuts()">
                                            <i class="fas fa-keyboard"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary" id="sendBtn" title="Send message (Ctrl+Enter)">
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
const container = document.getElementById('messagesContainer');
const messageInput = document.getElementById('messageInput');
const messageForm = document.getElementById('messageForm');
const sendBtn = document.getElementById('sendBtn');

// Auto-scroll to bottom
function scrollToBottom() {
    if (container) {
        container.scrollTop = container.scrollHeight;
    }
}
scrollToBottom();

// Handle input
if (messageInput) {
    messageInput.addEventListener('keydown', function(e) {
        if ((e.ctrlKey || e.metaKey) && e.key === 'Enter') {
            e.preventDefault();
            sendMessage();
        }
    });
}

// Send message
function sendMessage() {
    const body = messageInput.value.trim();
    if (!body || sendBtn.disabled) return;

    sendBtn.disabled = true;
    const originalText = sendBtn.innerHTML;
    sendBtn.innerHTML = '<i class="fas fa-spinner icon"></i><span>Sending...</span>';

    fetch('{{ route("messages.store", $conversation) }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ body })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            messageInput.value = '';
            location.reload();
        } else {
            alert('Error: ' + (data.error || 'Failed to send'));
            sendBtn.disabled = false;
            sendBtn.innerHTML = originalText;
        }
    })
    .catch(err => {
        console.error(err);
        alert('Failed to send message');
        sendBtn.disabled = false;
        sendBtn.innerHTML = originalText;
    });
}

// Message actions
function editMessage(id) {
    const newBody = prompt('Edit message:');
    if (newBody?.trim()) {
        fetch(`/messages/${id}`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ body: newBody })
        }).then(r => r.json()).then(d => d.success && location.reload());
    }
}

function deleteMessage(id) {
    if (confirm('Delete this message?')) {
        fetch(`/messages/${id}`, {
            method: 'DELETE',
            headers: {'X-CSRF-TOKEN': '{{ csrf_token() }}'}
        }).then(r => r.json()).then(d => d.success && location.reload());
    }
}

// Form submission
if (messageForm) {
    messageForm.addEventListener('submit', e => {
        e.preventDefault();
        sendMessage();
    });
}

// Auto-refresh
setInterval(() => {
    if (!messageForm) return;
    fetch('{{ route("conversations.show", $conversation) }}')
        .then(r => r.text())
        .then(html => {
            const newCount = html.match(/(\d+) messages/)?.[1];
            if (newCount && parseInt(newCount) > parseInt('{{ $conversation->messages->count() }}')) {
                location.reload();
            }
        })
        .catch(() => {});
}, 10000);

// Focus on load
setTimeout(() => messageInput?.focus(), 300);
</script>
@endsection
@endsection