<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;

class UserProfile extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'company_name',
        'company_registration_number',
        'tax_id',
        'website',
        'emergency_contact_name',
        'emergency_contact_phone',
        'preferences',
        'notification_settings',
        'wallet_balance',
        'credit_limit',
        'referral_code',
        'referred_by',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'wallet_balance' => 'decimal:2',
        'credit_limit' => 'decimal:2',
        'preferences' => 'array',
        'notification_settings' => 'array',
    ];

    /**
     * Get the user that owns the profile.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the referrer user.
     */
    public function referrer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'referred_by');
    }

    /**
     * Get users referred by this profile.
     */
    public function referredUsers(): HasMany
    {
        return $this->hasMany(UserProfile::class, 'referred_by');
    }

    /**
     * Update wallet balance.
     */
    public function updateWalletBalance(float $amount, string $type = 'add'): bool
    {
        if ($type === 'add') {
            $this->wallet_balance += $amount;
        } elseif ($type === 'subtract') {
            if ($this->wallet_balance < $amount) {
                return false; // Insufficient funds
            }
            $this->wallet_balance -= $amount;
        }

        return $this->save();
    }

    /**
     * Check if user has sufficient wallet balance.
     */
    public function hasSufficientBalance(float $amount): bool
    {
        return $this->wallet_balance >= $amount;
    }

    /**
     * Get notification setting value.
     */
    public function getNotificationSetting(string $key, mixed $default = null): mixed
    {
        return data_get($this->notification_settings, $key, $default);
    }

    /**
     * Set notification setting value.
     */
    public function setNotificationSetting(string $key, mixed $value): void
    {
        $settings = $this->notification_settings ?? [];
        data_set($settings, $key, $value);
        $this->notification_settings = $settings;
        $this->save();
    }

    /**
     * Get preference value.
     */
    public function getPreference(string $key, mixed $default = null): mixed
    {
        return data_get($this->preferences, $key, $default);
    }

    /**
     * Set preference value.
     */
    public function setPreference(string $key, mixed $value): void
    {
        $preferences = $this->preferences ?? [];
        data_set($preferences, $key, $value);
        $this->preferences = $preferences;
        $this->save();
    }

    /**
     * Get formatted wallet balance.
     */
    public function getFormattedWalletBalanceAttribute(): string
    {
        return number_format($this->wallet_balance, 2);
    }

    /**
     * Get referral earnings from referred users.
     */
    public function getReferralEarningsAttribute(): float
    {
        // This would be calculated based on actual referral commission logic
        // For now, return a placeholder
        return 0.00;
    }
}