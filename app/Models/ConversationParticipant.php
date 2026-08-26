<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ConversationParticipant extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'conversation_id',
        'user_id',
        'customer_id',
        'role',
        'is_muted',
        'joined_at',
        'left_at',
        'last_read_at',
    ];

    protected $casts = [
        'is_muted' => 'boolean',
        'joined_at' => 'datetime',
        'left_at' => 'datetime',
        'last_read_at' => 'datetime',
    ];

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(Conversation::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Get unread count for this participant.
     */
    public function getUnreadCount(): int
    {
        $readAfter = $this->last_read_at ?? $this->joined_at;

        return $this->conversation
            ->messages()
            ->where('created_at', '>', $readAfter)
            ->where('user_id', '!=', $this->user_id)
            ->count();
    }

    /**
     * Toggle mute.
     */
    public function toggleMute(): void
    {
        $this->update(['is_muted' => !$this->is_muted]);
    }
}