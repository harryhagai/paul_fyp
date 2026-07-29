<?php

namespace App\Models;

use App\Models\Concerns\HasPublicId;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Order extends Model
{
    use HasFactory, HasPublicId;

    protected $fillable = [
        'public_id',
        'order_number',
        'user_id',
        'status',
        'subtotal',
        'discount_amount',
        'total_amount',
        'currency',
        'customer_name',
        'customer_email',
        'customer_phone',
        'payment_provider',
        'payment_status',
        'clickpesa_client_id',
        'clickpesa_channel',
        'clickpesa_payment_id',
        'clickpesa_payment_reference',
        'payment_message',
        'clickpesa_payload',
        'paid_at',
        'payment_failed_at',
        'admin_notes',
        'estimated_delivery_date',
        'stock_deducted_at',
        'ordered_at',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'clickpesa_payload' => 'array',
        'paid_at' => 'datetime',
        'payment_failed_at' => 'datetime',
        'ordered_at' => 'datetime',
        'estimated_delivery_date' => 'date',
        'stock_deducted_at' => 'datetime',
    ];

    /**
     * Generate unique order number
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($order) {
            if (empty($order->order_number)) {
                $order->order_number = 'ORD-' . date('Y') . '-' . strtoupper(Str::random(8));
            }
        });
    }

    /**
     * Relationship with User
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relationship with OrderItems
     */
    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Relationship with OrderAddresses
     */
    public function orderAddresses(): HasMany
    {
        return $this->hasMany(OrderAddress::class);
    }

    /**
     * Get shipping address attribute
     */
    public function getShippingAddressAttribute()
    {
        return $this->orderAddresses()->where('type', 'shipping')->first();
    }

    /**
     * Get billing address attribute
     */
    public function getBillingAddressAttribute()
    {
        return $this->orderAddresses()->where('type', 'billing')->first();
    }

    /**
     * Check if order can be cancelled
     */
    public function canBeCancelled()
    {
        return in_array($this->status, ['pending', 'confirmed']);
    }

    /**
     * Check if order can be updated
     */
    public function canBeUpdated()
    {
        return !in_array($this->status, ['completed', 'cancelled']);
    }

    /**
     * Scope for user's orders
     */
    public function scopeForUser(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope for orders by status
     */
    public function scopeByStatus(Builder $query, string $status): Builder
    {
        return $query->where('status', $status);
    }

    /**
     * Scope for recent orders
     */
    public function scopeRecent(Builder $query, int $days = 30): Builder
    {
        return $query->where('ordered_at', '>=', now()->subDays($days));
    }

    /**
     * Get status color for display
     */
    public function getStatusColorAttribute()
    {
        return match($this->status) {
            'pending' => 'warning',
            'confirmed' => 'info',
            'completed' => 'success',
            'cancelled' => 'danger',
            default => 'secondary'
        };
    }

    /**
     * Get status text for display
     */
    public function getStatusTextAttribute()
    {
        return ucwords(str_replace('_', ' ', $this->status));
    }

    /**
     * Get formatted total amount
     */
    public function getFormattedTotalAttribute()
    {
        return $this->currency . ' ' . number_format($this->total_amount, 2);
    }
}
