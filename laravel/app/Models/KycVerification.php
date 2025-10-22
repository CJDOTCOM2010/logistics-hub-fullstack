<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;

class KycVerification extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'verification_type',
        'status',
        'document_type',
        'document_number',
        'document_expiry_date',
        'document_issue_date',
        'issuing_authority',
        'document_images',
        'selfie_image',
        'facial_match_score',
        'verification_notes',
        'rejection_reason',
        'verified_by',
        'verified_at',
        'expires_at',
        'third_party_verification_data',
        'verification_reference',
        'retry_count',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'document_expiry_date' => 'date',
        'document_issue_date' => 'date',
        'document_images' => 'array',
        'facial_match_score' => 'decimal:2',
        'verified_at' => 'datetime',
        'expires_at' => 'datetime',
        'third_party_verification_data' => 'array',
        'retry_count' => 'integer',
    ];

    /**
     * Get the user being verified.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the admin who verified the document.
     */
    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    /**
     * Check if verification is approved.
     */
    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    /**
     * Check if verification is pending.
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Check if verification is rejected.
     */
    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    /**
     * Check if verification is expired.
     */
    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    /**
     * Check if verification is currently valid.
     */
    public function isValid(): bool
    {
        return $this->isApproved() && !$this->isExpired();
    }

    /**
     * Approve the verification.
     */
    public function approve(User $verifier, ?\Carbon\Carbon $expiresAt = null, string $notes = ''): void
    {
        $this->update([
            'status' => 'approved',
            'verified_by' => $verifier->id,
            'verified_at' => now(),
            'expires_at' => $expiresAt,
            'verification_notes' => $notes,
        ]);
    }

    /**
     * Reject the verification.
     */
    public function reject(User $verifier, string $reason): void
    {
        $this->update([
            'status' => 'rejected',
            'verified_by' => $verifier->id,
            'rejection_reason' => $reason,
        ]);
    }

    /**
     * Request resubmission.
     */
    public function requestResubmission(string $reason): void
    {
        $this->update([
            'status' => 'requires_resubmission',
            'rejection_reason' => $reason,
            'retry_count' => $this->retry_count + 1,
        ]);
    }

    /**
     * Get the status label.
     */
    public function getStatusLabelAttribute(): string
    {
        return str_replace('_', ' ', title_case($this->status));
    }

    /**
     * Get the verification type label.
     */
    public function getVerificationTypeLabelAttribute(): string
    {
        return str_replace('_', ' ', title_case($this->verification_type));
    }

    /**
     * Get document URLs as array.
     */
    public function getDocumentUrlsAttribute(): array
    {
        return $this->document_images ?? [];
    }

    /**
     * Scope to get pending verifications.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope to get approved verifications.
     */
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    /**
     * Scope to get verifications by type.
     */
    public function scopeByType($query, string $type)
    {
        return $query->where('verification_type', $type);
    }

    /**
     * Scope to get expired verifications.
     */
    public function scopeExpired($query)
    {
        return $query->whereNotNull('expires_at')
                    ->where('expires_at', '<', now());
    }

    /**
     * Scope to get valid verifications.
     */
    public function scopeValid($query)
    {
        return $query->where('status', 'approved')
                    ->where(function ($q) {
                        $q->whereNull('expires_at')
                          ->orWhere('expires_at', '>', now());
                    });
    }
}