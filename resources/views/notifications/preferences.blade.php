@extends('layouts.app')

@section('title', 'Notification Preferences')
@section('page-title', 'Notification Preferences')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-light">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Manage your alerts</h5>
                    <a href="{{ route('notifications.index') }}" class="btn btn-sm btn-outline-secondary">
                        <i class="fas fa-arrow-left me-1"></i> Back to notifications
                    </a>
                </div>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('notifications.updatePreferences') }}">
                    @csrf

                    @php
                        $preferences = auth()->user()->getNotificationPreferences();
                        $options = [
                            'low_stock' => 'Low stock alerts',
                            'overdue_payment' => 'Overdue payment alerts',
                            'payment_received' => 'Payment received alerts',
                            'sale_created' => 'New sale alerts',
                            'expense_approved' => 'Expense approval alerts',
                            'email_notifications' => 'Email notifications',
                        ];
                    @endphp

                    @foreach($options as $key => $label)
                        <div class="border rounded p-3 mb-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="fw-semibold">{{ $label }}</div>
                                    <small class="text-muted">Toggle this alert on or off.</small>
                                </div>
                                <div class="form-check form-switch">
                                    <input
                                        class="form-check-input"
                                        type="checkbox"
                                        name="{{ $key }}"
                                        value="1"
                                        {{ ($preferences[$key] ?? true) ? 'checked' : '' }}
                                    >
                                </div>
                            </div>
                        </div>
                    @endforeach

                    <div class="d-flex justify-content-end mt-4">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i> Save Preferences
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
