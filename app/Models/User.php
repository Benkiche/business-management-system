<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
        'role_id',
        'status',
        'profile_photo_path',
        'last_login_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'last_login_at' => 'datetime',
    ];

    /**
     * Get the role this user belongs to.
     */
    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    /**
     * Get all permissions for this user through their role.
     */
    public function permissions()
    {
        return $this->role->permissions ?? collect();
    }

    /**
     * Check if user has a specific permission.
     */
    public function hasPermission($permissionName): bool
    {
        if (!$this->role) {
            return false;
        }

        return $this->role->hasPermission($permissionName);
    }

    /**
     * Check if user has any of the given permissions.
     */
    public function hasAnyPermission(array $permissions): bool
    {
        if (!$this->role) {
            return false;
        }

        return $this->role->hasAnyPermission($permissions);
    }

    /**
     * Check if user has all of the given permissions.
     */
    public function hasAllPermissions(array $permissions): bool
    {
        if (!$this->role) {
            return false;
        }

        return $this->role->hasAllPermissions($permissions);
    }

    /**
     * Check if user is super admin.
     */
    public function isSuperAdmin(): bool
    {
        return $this->role?->name === 'super_admin';
    }

    /**
     * Check if user is an admin.
     */
    public function isAdmin(): bool
    {
        return in_array($this->role?->name, ['super_admin', 'admin']);
    }

    /**
     * Check if user is a manager.
     */
    public function isManager(): bool
    {
        return $this->role?->name === 'manager';
    }

    /**
     * Check if user is active.
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Scope to get only active users.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope to filter by role.
     */
    public function scopeByRole($query, $roleId)
    {
        return $query->where('role_id', $roleId);
    }

    /**
     * Get user's notifications.
     */
    public function notifications()
    {
        return $this->hasMany(Notification::class)->latest();
    }

    /**
     * Get unread notifications count.
     */
    public function unreadNotificationsCount(): int
    {
        return $this->notifications()->unread()->count();
    }

    /**
     * Get notification preferences.
     */
    public function getNotificationPreferences(): array
    {
        return json_decode($this->notification_preferences ?? '{}', true);
    }

    /**
     * Update notification preferences.
     */
    public function updateNotificationPreferences(array $preferences): void
    {
        $current = $this->getNotificationPreferences();
        $this->update(['notification_preferences' => json_encode(array_merge($current, $preferences))]);
    }

    /**
     * Check if notification category is enabled.
     */
    public function isNotificationEnabled($category): bool
    {
        $preferences = $this->getNotificationPreferences();
        return $preferences[$category] ?? true;
    }



// Add these relationships to User model:

/**
 * Conversations where user is participant.
 */
public function conversations(): BelongsToMany
{
    return $this->belongsToMany(Conversation::class, 'conversation_participants')
    ->withPivot('role', 'is_muted', 'joined_at', 'last_read_at');
}

/**
 * Conversations created by user.
 */
public function createdConversations(): HasMany
{
    return $this->hasMany(Conversation::class, 'created_by');
}

/**
 * Messages sent by user.
 */
public function messages(): HasMany
{
    return $this->hasMany(Message::class);
}

/**
 * Message reads by user.
 */
public function messageReads(): HasMany
{
    return $this->hasMany(MessageRead::class);
}

/**
 * Get unread conversation count.
 */
public function getUnreadConversationCount(): int
{
    return $this->conversations()
        ->where(function ($query) {
            $query->whereNull('last_message_at')
                ->orWhereRaw('last_message_at > conversation_participants.last_read_at');
        })
        ->count();
}

/**
 * Get online status badge.
 */
public function getOnlineStatusBadge(): string
{
    return match($this->online_status) {
        'online' => '<span class="badge bg-success">Online</span>',
        'away' => '<span class="badge bg-warning">Away</span>',
        'do_not_disturb' => '<span class="badge bg-danger">DND</span>',
        default => '<span class="badge bg-secondary">Offline</span>',
    };
}

/**
 * Set user online.
 */
public function setOnline(): void
{
    $this->update([
        'online_status' => 'online',
        'last_seen_at' => now(),
    ]);
}

/**
 * Set user offline.
 */
public function setOffline(): void
{
    $this->update([
        'online_status' => 'offline',
        'last_seen_at' => now(),
    ]);
}
}
