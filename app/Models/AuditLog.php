<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AuditLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'action',
        'entity_type',
        'entity_id',
        'description',
        'old_values',
        'new_values',
        'ip_address',
        'user_agent',
        'status',
        'error_message',
        'action_at',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
        'action_at' => 'datetime',
    ];

    /**
     * Get the user who performed the action.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope to filter by entity type.
     */
    public function scopeForEntity($query, $entityType, $entityId = null)
    {
        $query->where('entity_type', $entityType);
        if ($entityId) {
            $query->where('entity_id', $entityId);
        }
        return $query;
    }

    /**
     * Scope to filter by action.
     */
    public function scopeByAction($query, $action)
    {
        return $query->where('action', $action);
    }

    /**
     * Scope to filter by date range.
     */
    public function scopeDateRange($query, $from, $to)
    {
        return $query->whereBetween('action_at', [$from, $to]);
    }

    /**
     * Get human-readable action description.
     */
    public function getActionLabelAttribute(): string
    {
        return match($this->action) {
            'created' => 'Created',
            'updated' => 'Updated',
            'deleted' => 'Deleted',
            'viewed' => 'Viewed',
            'exported' => 'Exported',
            'approved' => 'Approved',
            'rejected' => 'Rejected',
            'cancelled' => 'Cancelled',
            'restored' => 'Restored',
            default => ucfirst($this->action),
        };
    }

    /**
     * Get human-readable entity type.
     */
    public function getEntityLabelAttribute(): string
    {
        return match($this->entity_type) {
            'Sale' => 'Sale Invoice',
            'Payment' => 'Payment',
            'Expense' => 'Expense',
            'Product' => 'Product',
            'Customer' => 'Customer',
            'Supplier' => 'Supplier',
            'User' => 'User Account',
            'Role' => 'Role',
            'Category' => 'Category',
            default => $this->entity_type,
        };
    }

    /**
     * Get badge color for status.
     */
    public function getStatusBadgeAttribute(): string
    {
        return $this->status === 'success' ? 'success' : 'danger';
    }
}