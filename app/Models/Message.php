<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Message extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'conversation_id',
        'user_id',
        'customer_id',
        'body',
        'type',
        'file_path',
        'file_type',
        'file_size',
        'is_edited',
        'edited_at',
        'reaction_count',
    ];

    protected $casts = [
        'is_edited' => 'boolean',
        'edited_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected $appends = ['is_read', 'sender_name', 'sender_type'];

    /**
     * Conversation relationship.
     */
    public function conversation(): BelongsTo
    {
        return $this->belongsTo(Conversation::class);
    }

    /**
     * Sender (User) relationship.
     */
    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Customer sender relationship.
     */
    public function customerSender(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    /**
     * Message reads relationship.
     */
    public function reads(): HasMany
    {
        return $this->hasMany(MessageRead::class);
    }

    /**
     * Get read count.
     */
    public function getReadCount(): int
    {
        return $this->reads()->count();
    }

    /**
     * Check if read by user.
     */
    public function isReadBy(User $user): bool
    {
        return $this->reads()->where('user_id', $user->id)->exists();
    }

    /**
     * Mark as read.
     */
    public function markAsRead(User $user): void
    {
        if (!$this->isReadBy($user)) {
            $this->reads()->create(['user_id' => $user->id]);
        }
    }

    /**
     * Check if can be edited by user.
     */
    public function canBeEditedBy(User $user): bool
    {
        return $user->is($this->sender) || $user->isAdmin();
    }

    /**
     * Check if can be deleted by user.
     */
    public function canBeDeletedBy(User $user): bool
    {
        return $user->is($this->sender) || $user->isAdmin();
    }

    /**
     * Get sender name.
     */
    public function getSenderNameAttribute(): string
    {
        if ($this->user_id) {
            return $this->sender->name ?? 'Unknown User';
        } elseif ($this->customer_id) {
            return $this->customerSender->name ?? 'Unknown Customer';
        }
        return 'System';
    }

    /**
     * Get sender type.
     */
    public function getSenderTypeAttribute(): string
    {
        if ($this->type === 'system') {
            return 'system';
        } elseif ($this->user_id) {
            return 'staff';
        } elseif ($this->customer_id) {
            return 'customer';
        }
        return 'unknown';
    }

    /**
     * Get is read (for current auth user).
     */
    public function getIsReadAttribute(): bool
    {
        if (!auth()->check()) {
            return false;
        }
        return $this->isReadBy(auth()->user());
    }

    /**
     * Scope: Recent messages.
     */
    public function scopeRecent($query, $minutes = 1440)
    {
        return $query->where('created_at', '>=', now()->subMinutes($minutes));
    }

    /**
     * Scope: System messages.
     */
    public function scopeSystem($query)
    {
        return $query->where('type', 'system');
    }

    /**
     * Scope: User messages.
     */
    public function scopeUserMessages($query)
    {
        return $query->where('type', '!=', 'system')->whereNotNull('user_id');
    }
}