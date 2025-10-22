<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;

class Vehicle extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'vehicle_number',
        'make',
        'model',
        'year',
        'vehicle_type',
        'fuel_type',
        'license_plate',
        'vin_number',
        'capacity_weight',
        'capacity_volume',
        'axles',
        'status',
        'ownership',
        'driver_id',
        'owner_id',
        'features',
        'registration_expiry',
        'insurance_expiry',
        'last_service_date',
        'next_service_date',
        'mileage',
        'fuel_efficiency',
        'documents',
        'gps_enabled',
        'gps_device_id',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'capacity_weight' => 'decimal:2',
        'capacity_volume' => 'decimal:2',
        'mileage' => 'integer',
        'fuel_efficiency' => 'decimal:2',
        'features' => 'array',
        'documents' => 'array',
        'gps_enabled' => 'boolean',
        'registration_expiry' => 'date',
        'insurance_expiry' => 'date',
        'last_service_date' => 'date',
        'next_service_date' => 'date',
        'year' => 'integer',
        'axles' => 'integer',
    ];

    /**
     * Get the driver assigned to the vehicle.
     */
    public function driver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'driver_id');
    }

    /**
     * Get the owner of the vehicle.
     */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    /**
     * Get shipments assigned to this vehicle.
     */
    public function shipments(): HasMany
    {
        return $this->hasMany(Shipment::class);
    }

    /**
     * Get the vehicle's full designation.
     */
    public function getFullDesignationAttribute(): string
    {
        return "{$this->year} {$this->make} {$this->model} ({$this->license_plate})";
    }

    /**
     * Get the vehicle's type label.
     */
    public function getVehicleTypeLabelAttribute(): string
    {
        return str_replace('_', ' ', title_case($this->vehicle_type));
    }

    /**
     * Get the vehicle's status label.
     */
    public function getStatusLabelAttribute(): string
    {
        return str_replace('_', ' ', title_case($this->status));
    }

    /**
     * Check if vehicle is available for assignment.
     */
    public function isAvailable(): bool
    {
        return $this->status === 'available';
    }

    /**
     * Check if vehicle registration is expired.
     */
    public function isRegistrationExpired(): bool
    {
        return $this->registration_expiry && $this->registration_expiry->isPast();
    }

    /**
     * Check if vehicle insurance is expired.
     */
    public function isInsuranceExpired(): bool
    {
        return $this->insurance_expiry && $this->insurance_expiry->isPast();
    }

    /**
     * Check if vehicle needs service.
     */
    public function needsService(): bool
    {
        return $this->next_service_date && $this->next_service_date->isPast();
    }

    /**
     * Get days until registration expiry.
     */
    public function getDaysUntilRegistrationExpiryAttribute(): ?int
    {
        return $this->registration_expiry
            ? now()->diffInDays($this->registration_expiry, false)
            : null;
    }

    /**
     * Get days until insurance expiry.
     */
    public function getDaysUntilInsuranceExpiryAttribute(): ?int
    {
        return $this->insurance_expiry
            ? now()->diffInDays($this->insurance_expiry, false)
            : null;
    }

    /**
     * Get days until next service.
     */
    public function getDaysUntilNextServiceAttribute(): ?int
    {
        return $this->next_service_date
            ? now()->diffInDays($this->next_service_date, false)
            : null;
    }

    /**
     * Assign driver to vehicle.
     */
    public function assignDriver(User $driver): void
    {
        $this->driver()->associate($driver);
        $this->status = 'in_use';
        $this->save();
    }

    /**
     * Unassign driver from vehicle.
     */
    public function unassignDriver(): void
    {
        $this->driver()->dissociate();
        $this->status = 'available';
        $this->save();
    }

    /**
     * Update vehicle mileage.
     */
    public function updateMileage(int $newMileage): void
    {
        if ($newMileage > $this->mileage) {
            $this->mileage = $newMileage;
            $this->save();
        }
    }

    /**
     * Calculate fuel consumption for a trip.
     */
    public function calculateFuelConsumption(float $distanceKm): float
    {
        if (!$this->fuel_efficiency) {
            return 0;
        }

        return $distanceKm / $this->fuel_efficiency;
    }

    /**
     * Get the vehicle's current location from the driver.
     */
    public function getCurrentLocationAttribute(): ?array
    {
        return $this->driver?->current_location;
    }

    /**
     * Scope to get available vehicles.
     */
    public function scopeAvailable($query)
    {
        return $query->where('status', 'available');
    }

    /**
     * Scope to get vehicles by type.
     */
    public function scopeByType($query, string $type)
    {
        return $query->where('vehicle_type', $type);
    }

    /**
     * Scope to get vehicles that need service.
     */
    public function scopeNeedsService($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('next_service_date')
              ->orWhere('next_service_date', '<=', now());
        });
    }

    /**
     * Scope to get vehicles with expired documents.
     */
    public function scopeWithExpiredDocuments($query)
    {
        return $query->where(function ($q) {
            $q->where(function ($subQuery) {
                $subQuery->whereNotNull('registration_expiry')
                         ->where('registration_expiry', '<', now());
            })->orWhere(function ($subQuery) {
                $subQuery->whereNotNull('insurance_expiry')
                         ->where('insurance_expiry', '<', now());
            });
        });
    }

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        // When assigning a driver, update their status
        static::updated(function ($vehicle) {
            if ($vehicle->wasChanged('driver_id')) {
                if ($vehicle->driver_id) {
                    $vehicle->driver->update(['user_type' => 'driver']);
                }
            }
        });
    }
}