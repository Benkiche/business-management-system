@extends('layouts.app')

@section('title', 'Start New Conversation')
@section('page-title', 'Start New Conversation')

@section('content')
<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Create Conversation</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('conversations.store') }}" novalidate>
                    @csrf

                    <!-- Subject -->
                    <div class="mb-3">
                        <label for="subject" class="form-label fw-semibold">Subject <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('subject') is-invalid @enderror" 
                               id="subject" name="subject" placeholder="Enter conversation subject"
                               value="{{ old('subject') }}" required>
                        @error('subject')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Type -->
                    <div class="mb-3">
                        <label for="type" class="form-label fw-semibold">Type <span class="text-danger">*</span></label>
                        <select class="form-select @error('type') is-invalid @enderror" 
                                id="type" name="type" required>
                            <option value="">-- Select Type --</option>
                            <option value="customer_support" {{ old('type') === 'customer_support' ? 'selected' : '' }}>
                                Customer Support
                            </option>
                            <option value="internal" {{ old('type') === 'internal' ? 'selected' : '' }}>
                                Internal Discussion
                            </option>
                            <option value="sales" {{ old('type') === 'sales' ? 'selected' : '' }}>
                                Sales Query
                            </option>
                            <option value="billing" {{ old('type') === 'billing' ? 'selected' : '' }}>
                                Billing Issue
                            </option>
                        </select>
                        @error('type')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Priority -->
                    <div class="mb-3">
                        <label for="priority" class="form-label fw-semibold">Priority</label>
                        <select class="form-select @error('priority') is-invalid @enderror" 
                                id="priority" name="priority">
                            <option value="1" {{ old('priority') === '1' ? 'selected' : '' }}>
                                Low Priority
                            </option>
                            <option value="2" {{ old('priority') === '2' ? 'selected' : '' }}>
                                Medium Priority
                            </option>
                            <option value="3" {{ old('priority') === '3' ? 'selected' : '' }}>
                                High Priority (Urgent)
                            </option>
                        </select>
                        @error('priority')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Customer (Optional) -->
                    <div class="mb-3">
                        <label for="customer_id" class="form-label fw-semibold">Related Customer (Optional)</label>
                        <select class="form-select @error('customer_id') is-invalid @enderror" 
                                id="customer_id" name="customer_id">
                            <option value="">-- No Customer --</option>
                            @foreach($customers as $customer)
                                <option value="{{ $customer->id }}" {{ old('customer_id') == $customer->id ? 'selected' : '' }}>
                                    {{ $customer->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('customer_id')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Description -->
                    <div class="mb-3">
                        <label for="description" class="form-label fw-semibold">Description (Optional)</label>
                        <textarea class="form-control @error('description') is-invalid @enderror" 
                                  id="description" name="description" rows="4" 
                                  placeholder="Provide context for this conversation...">{{ old('description') }}</textarea>
                        @error('description')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Participants -->
                    <div class="mb-4">
                        <label class="form-label fw-semibold">Add Participants <span class="text-danger">*</span></label>
                        <small class="d-block text-muted mb-2">Select at least one person to add to this conversation</small>
                        
                        <div class="row">
                            @forelse($users as $user)
                                <div class="col-md-6 mb-2">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" 
                                               name="participants[]" value="{{ $user->id }}" 
                                               id="user_{{ $user->id }}"
                                               {{ in_array($user->id, old('participants', [])) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="user_{{ $user->id }}">
                                            <strong>{{ $user->name }}</strong>
                                            <small class="text-muted d-block">{{ $user->role->name ?? 'User' }}</small>
                                        </label>
                                    </div>
                                </div>
                            @empty
                                <div class="col-12">
                                    <p class="text-muted">No other users available</p>
                                </div>
                            @endforelse
                        </div>

                        @error('participants')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                        @error('participants.*')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Buttons -->
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-plus me-2"></i> Create Conversation
                        </button>
                        <a href="{{ route('conversations.index') }}" class="btn btn-secondary">
                            <i class="fas fa-times me-2"></i> Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Helper Sidebar -->
    <div class="col-lg-4">
        <div class="card mb-3">
            <div class="card-header bg-light">
                <h6 class="mb-0"><i class="fas fa-info-circle me-2"></i>About Conversations</h6>
            </div>
            <div class="card-body small text-muted">
                <p>Conversations allow team members to collaborate and discuss specific topics.</p>
                
                <h6 class="mt-3 mb-2">Types:</h6>
                <ul class="small">
                    <li><strong>Customer Support:</strong> For customer-related issues</li>
                    <li><strong>Internal:</strong> Team discussions and planning</li>
                    <li><strong>Sales:</strong> Sales opportunities and queries</li>
                    <li><strong>Billing:</strong> Payment and invoicing issues</li>
                </ul>

                <h6 class="mt-3 mb-2">Priority Levels:</h6>
                <ul class="small">
                    <li><span class="badge bg-secondary">Low:</span> Can wait</li>
                    <li><span class="badge bg-warning text-dark">Medium:</span> Important</li>
                    <li><span class="badge bg-danger">High:</span> Urgent</li>
                </ul>
            </div>
        </div>

        <div class="card">
            <div class="card-header bg-light">
                <h6 class="mb-0"><i class="fas fa-lightbulb me-2"></i>Tips</h6>
            </div>
            <div class="card-body small text-muted">
                <ul>
                    <li>Use clear, descriptive subjects</li>
                    <li>Add relevant participants only</li>
                    <li>Include context in description</li>
                    <li>Link to customers when applicable</li>
                </ul>
            </div>
        </div>
    </div>
</div>

@section('js')
<script>
// Basic validation
document.querySelector('form').addEventListener('submit', function(e) {
    const participants = document.querySelectorAll('input[name="participants[]"]:checked');
    if (participants.length === 0) {
        e.preventDefault();
        alert('Please select at least one participant');
    }
});
</script>
@endsection
@endsection