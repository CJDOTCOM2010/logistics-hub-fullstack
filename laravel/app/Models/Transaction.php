<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'transaction_id',
        'user_id',
        'shipment_id',
        'transaction_type',
        'payment_method',
        'status',
        'amount',
        'currency',
        'fee',
        'tax',
        'description',
        'payment_gateway_data',
        'gateway_transaction_id',
        'gateway_reference',
        'gateway_status',
        'processed_at',
        'failed_at',
        'failure_reason',
        'metadata',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'amount' => 'decimal:2',
        'fee' => 'decimal:2',
        'tax' => 'decimal:2',
        'net_amount' => 'decimal:2',
        'payment_gateway_data' => 'array',
        'metadata' => 'array',
        'processed_at' => 'datetime',
        'failed_at' => 'datetime',
    ];

    /**
     * Get the user associated with the transaction.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the shipment associated with the transaction.
     */
    public function shipment(): BelongsTo
    {
        return $this->belongsTo(Shipment::class);
    }

    /**
     * Get the transaction's type label.
     */
    public function getTransactionTypeLabelAttribute(): string
    {
        return str_replace('_', ' ', title_case($this->transaction_type));
    }

    /**
     * Get the transaction's status label.
     */
    public function getStatusLabelAttribute(): string
    {
        return str_replace('_', ' ', title_case($this->status));
    }

    /**
     * Get the payment method label.
     */
    public function getPaymentMethodLabelAttribute(): string
    {
        return str_replace('_', ' ', title_case($this->payment_method));
    }

    /**
     * Get formatted amount.
     */
    public function getFormattedAmountAttribute(): string
    {
        return number_format($this->amount, 2) . ' ' . $this->currency;
    }

    /**
     * Get formatted net amount.
     */
    public function getFormattedNetAmountAttribute(): string
    {
        return number_format($this->net_amount, 2) . ' ' . $this->currency;
    }

    /**
     * Check if transaction is completed successfully.
     */
    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    /**
     * Check if transaction is pending.
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Check if transaction failed.
     */
    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }

    /**
     * Mark transaction as completed.
     */
    public function markAsCompleted(): void
    {
        $this->update([
            'status' => 'completed',
            'processed_at' => now(),
        ]);
    }

    /**
     * Mark transaction as failed.
     */
    public function markAsFailed(string $reason): void
    {
        $this->update([
            'status' => 'failed',
            'failed_at' => now(),
            'failure_reason' => $reason,
        ]);
    }

    /**
     * Process refund.
     */
    public function processRefund(): void
    {
        if ($this->isCompleted()) {
            // Create refund transaction
            static::create([
                'transaction_id' => 'REF' . date('YmdHis') . mt_rand(100, 999),
                'user_id' => $this->user_id,
                'shipment_id' => $this->shipment_id,
                'transaction_type' => 'refund',
                'payment_method' => $this->payment_method,
                'status' => 'pending',
                'amount' => $this->net_amount,
                'currency' => $this->currency,
                'description' => "Refund for transaction {$this->transaction_id}",
                'metadata' => [
                    'original_transaction_id' => $this->id,
                    'original_transaction_ref' => $this->transaction_id,
                ],
            ]);

            // Update original transaction status
            $this->update(['status' => 'refunded']);
        }
    }

    /**
     * Scope to get transactions by type.
     */
    public function scopeByType($query, string $type)
    {
        return $query->where('transaction_type', $type);
    }

    /**
     * Scope to get transactions by status.
     */
    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope to get transactions by user.
     */
    public function scopeByUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope to get completed transactions.
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope to get pending transactions.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope to get failed transactions.
     */
    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        // Generate transaction ID on creation
        static::creating(function ($transaction) {
            if (!$transaction->transaction_id) {
                $prefix = strtoupper(substr($transaction->transaction_type, 0, 3));
                $transaction->transaction_id = $prefix . date('YmdHis') . mt_rand(100, 999);
            }
        });
    }
}