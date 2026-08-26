<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
	use HasFactory;

	protected $fillable = ['user_id', 'type', 'title', 'message', 'icon', 'action_url', 'category', 'read_at', 'sent_email', 'email_sent_at'];

	protected $casts = [
		'read_at' => 'datetime',
		'email_sent_at' => 'datetime',
		'sent_email' => 'boolean',
	];

	public function user()
	{
		return $this->belongsTo(User::class);
	}

	public function markAsRead(): void
	{
		if (! $this->read_at) {
			$this->update(['read_at' => now()]);
		}
	}

	public function isRead(): bool
	{
		return $this->read_at !== null;
	}

	public function getIconClassAttribute(): string
	{
		return $this->icon ?: match ($this->type) {
			'warning', 'danger' => 'fas fa-exclamation-triangle',
			'success' => 'fas fa-check-circle',
			'info' => 'fas fa-info-circle',
			default => 'fas fa-bell',
		};
	}

	public function scopeUnread($query)
	{
		return $query->whereNull('read_at');
	}
}
