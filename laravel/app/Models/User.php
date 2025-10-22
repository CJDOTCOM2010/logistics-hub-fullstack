<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'phone',
        'phone_country_code',
        'password',
        'avatar',
        'user_type',
        'status',
        'is_online',
        'last_login_at',
        'last_login_ip',
        'timezone',
        'language',
        'date_of_birth',
        'gender',
        'bio',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'phone_verified_at' => 'datetime',
        'last_login_at' => 'datetime',
        'date_of_birth' => 'date',
        'is_online' => 'boolean',
        'password' => 'hashed',
    ];

    /**
     * Get the user's full name.
     */
    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }

    /**
     * Get the user's profile.
     */
    public function profile(): HasOne
    {
        return $this->hasOne(UserProfile::class);
    }

    /**
     * Get the user's addresses.
     */
    public function addresses(): HasMany
    {
        return $this->hasMany(Address::class);
    }

    /**
     * Get the user's primary address.
     */
    public function primaryAddress(): HasOne
    {
        return $this->hasOne(Address::class)->where('is_primary', true);
    }

    /**
     * Get vehicles owned by the user.
     */
    public function ownedVehicles(): HasMany
    {
        return $this->hasMany(Vehicle::class, 'owner_id');
    }

    /**
     * Get the vehicle assigned to the user (driver).
     */
    public function vehicle(): HasOne
    {
        return $this->hasOne(Vehicle::class, 'driver_id');
    }

    /**
     * Get shipments created by the user.
     */
    public function createdShipments(): HasMany
    {
        return $this->hasMany(Shipment::class, 'customer_id');
    }

    /**
     * Get shipments assigned to the user (driver/agent).
     */
    public function assignedShipments(): HasMany
    {
        return $this->hasMany(Shipment::class, 'driver_id');
    }

    /**
     * Get shipments managed by the user (agent).
     */
    public function managedShipments(): HasMany
    {
        return $this->hasMany(Shipment::class, 'agent_id');
    }

    /**
     * Get transactions for the user.
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    /**
     * Get KYC verifications for the user.
     */
    public function kycVerifications(): HasMany
    {
        return $this->hasMany(KycVerification::class);
    }

    /**
     * Get employee record for the user.
     */
    public function employee(): HasOne
    {
        return $this->hasOne(Employee::class);
    }

    /**
     * Get employees managed by the user.
     */
    public function managedEmployees(): HasMany
    {
        return $this->hasMany(Employee::class, 'manager_id');
    }

    /**
     * Get invoices created for the user.
     */
    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class, 'customer_id');
    }

    /**
     * Get invoices created by the user.
     */
    public function createdInvoices(): HasMany
    {
        return $this->hasMany(Invoice::class, 'created_by');
    }

    /**
     * Get conversations the user is part of.
     */
    public function conversations(): BelongsToMany
    {
        return $this->belongsToMany(Conversation::class, 'conversation_participants')
            ->withPivot('joined_at', 'left_at', 'role', 'is_muted', 'is_archived')
            ->withTimestamps();
    }

    /**
     * Get messages sent by the user.
     */
    public function sentMessages(): HasMany
    {
        return $this->hasMany(Message::class, 'sender_id');
    }

    /**
     * Get users referred by this user.
     */
    public function referredUsers(): HasMany
    {
        return $this->hasMany(User::class, 'referred_by');
    }

    /**
     * Get the user who referred this user.
     */
    public function referrer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'referred_by');
    }

    /**
     * Get user's wallet balance from profile.
     */
    public function getWalletBalanceAttribute(): float
    {
        return $this->profile?->wallet_balance ?? 0.00;
    }

    /**
     * Check if user is a driver.
     */
    public function isDriver(): bool
    {
        return $this->user_type === 'driver';
    }

    /**
     * Check if user is an admin.
     */
    public function isAdmin(): bool
    {
        return in_array($this->user_type, ['super_admin', 'admin']);
    }

    /**
     * Check if user is a customer.
     */
    public function isCustomer(): bool
    {
        return $this->user_type === 'customer';
    }

    /**
     * Check if user is an agent.
     */
    public function isAgent(): bool
    {
        return $this->user_type === 'agent';
    }

    /**
     * Check if user's KYC is verified.
     */
    public function isKycVerified(): bool
    {
        return $this->kycVerifications()
            ->where('verification_type', 'identity')
            ->where('status', 'approved')
            ->where(function ($query) {
                $query->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            })
            ->exists();
    }

    /**
     * Get the user's current location if they are a driver.
     */
    public function getCurrentLocationAttribute(): ?array
    {
        if (!$this->isDriver()) {
            return null;
        }

        $latestTracking = ShipmentTracking::where('user_id', $this->id)
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->latest()
            ->first();

        return $latestTracking ? [
            'latitude' => $latestTracking->latitude,
            'longitude' => $latestTracking->longitude,
            'location_name' => $latestTracking->location_name,
            'updated_at' => $latestTracking->created_at,
        ] : null;
    }

    /**
     * Update user's online status.
     */
    public function updateOnlineStatus(bool $isOnline): void
    {
        $this->update([
            'is_online' => $isOnline,
            'last_login_at' => $isOnline ? now() : $this->last_login_at,
        ]);
    }

    /**
     * Get user's dashboard URL based on user type.
     */
    public function getDashboardUrlAttribute(): string
    {
        return match ($this->user_type) {
            'super_admin' => '/admin/dashboard',
            'admin' => '/admin/dashboard',
            'driver' => '/driver/dashboard',
            'agent' => '/agent/dashboard',
            'customer' => '/customer/dashboard',
            'accountant' => '/accountant/dashboard',
            'hr' => '/hr/dashboard',
            default => '/dashboard',
        };
    }

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        // Create profile when user is created
        static::created(function ($user) {
            $user->profile()->create([
                'wallet_balance' => 0.00,
                'credit_limit' => 0.00,
                'referral_code' => strtoupper(substr(md5($user->id . $user->email), 0, 8)),
            ]);
        });
    }
}