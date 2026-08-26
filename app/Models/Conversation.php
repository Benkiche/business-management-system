<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Conversation extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'subject',
        'type',
        'created_by',
        'customer_id',
        'status',
        'description',
        'priority',
        'last_message_at',
    ];

    protected $casts = [
        'last_message_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Creator relationship.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Customer relationship.
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Participants relationship.
     */
    public function participants(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'conversation_participants')
            ->withPivot('role', 'is_muted', 'joined_at', 'last_read_at');
    }

    /**
     * Conversation participants model.
     */
    public function participantsModels(): HasMany
    {
        return $this->hasMany(ConversationParticipant::class);
    }

    /**
     * Messages relationship.
     */
    public function messages(): HasMany
    {
        return $this->hasMany(Message::class)->orderBy('created_at');
    }

    /**
     * Latest messages.
     */
    public function latestMessages($limit = 50): HasMany
    {
        return $this->messages()->latest()->take($limit);
    }

    /**
     * Get unread message count for user.
     */
    public function unreadCount(User $user): int
    {
        return $this->messages()
            ->whereDoesntHave('reads', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->where('user_id', '!=', $user->id)
            ->count();
    }

    /**
     * Add participant to conversation.
     */
    public function addParticipant(User $user, string $role = 'participant'): void
    {
        if (!$this->participants()->where('user_id', $user->id)->exists()) {
            $this->participants()->attach($user->id, [
                'role' => $role,
                'joined_at' => now(),
            ]);
        }
    }

    /**
     * Remove participant from conversation.
     */
    public function removeParticipant(User $user): void
    {
        $this->participants()->detach($user->id);
    }

    /**
     * Check if user can participate in conversation.
     */
    public function canParticipate(User $user): bool
    {
        // Admin/Manager can always participate
        if ($user->isAdmin() || $user->isManager()) {
            return true;
        }

        // Must be a participant
        return $this->participants()->where('user_id', $user->id)->exists();
    }

    /**
     * Check if user can delete conversation.
     */
    public function canDelete(User $user): bool
    {
        return $user->is($this->creator) || $user->isAdmin();
    }

    /**
     * Get participant role.
     */
    public function getParticipantRole(User $user): ?string
    {
        $participant = $this->participantsModels()
            ->where('user_id', $user->id)
            ->first();

        return $participant?->role;
    }

    /**
     * Scope: Open conversations.
     */
    public function scopeOpen($query)
    {
        return $query->where('status', 'open');
    }

    /**
     * Scope: By type.
     */
    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope: For user (as participant).
     */
    public function scopeForUser($query, User $user)
    {
        return $query->whereHas('participants', function ($q) use ($user) {
            $q->where('user_id', $user->id);
        });
    }

    /**
     * Scope: High priority.
     */
    public function scopeHighPriority($query)
    {
        return $query->where('priority', '>=', 2);
    }

    /**
     * Mark as read for user.
     */
    public function markAsRead(User $user): void
    {
        $this->participantsModels()
            ->where('user_id', $user->id)
            ->update(['last_read_at' => now()]);
    }
}