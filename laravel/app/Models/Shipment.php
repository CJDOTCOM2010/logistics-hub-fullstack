<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;

class Shipment extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'tracking_number',
        'customer_id',
        'agent_id',
        'driver_id',
        'vehicle_id',
        'pickup_address_id',
        'delivery_address_id',
        'shipment_type',
        'status',
        'priority',
        'description',
        'items',
        'weight',
        'dimensions_length',
        'dimensions_width',
        'dimensions_height',
        'declared_value',
        'shipping_cost',
        'insurance_cost',
        'total_cost',
        'distance',
        'estimated_duration',
        'pickup_scheduled_at',
        'pickup_completed_at',
        'delivery_scheduled_at',
        'delivery_completed_at',
        'special_instructions',
        'pickup_notes',
        'delivery_notes',
        'proof_of_delivery',
        'recipient_name',
        'recipient_phone',
        'signature_required',
        'insurance_requested',
        'payment_status',
        'payment_method',
        'external_reference',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'items' => 'array',
        'weight' => 'decimal:2',
        'dimensions_length' => 'decimal:2',
        'dimensions_width' => 'decimal:2',
        'dimensions_height' => 'decimal:2',
        'declared_value' => 'decimal:2',
        'shipping_cost' => 'decimal:2',
        'insurance_cost' => 'decimal:2',
        'total_cost' => 'decimal:2',
        'distance' => 'decimal:2',
        'estimated_duration' => 'integer',
        'pickup_scheduled_at' => 'datetime',
        'pickup_completed_at' => 'datetime',
        'delivery_scheduled_at' => 'datetime',
        'delivery_completed_at' => 'datetime',
        'proof_of_delivery' => 'array',
        'signature_required' => 'boolean',
        'insurance_requested' => 'boolean',
    ];

    /**
     * Get the customer who created the shipment.
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    /**
     * Get the agent managing the shipment.
     */
    public function agent(): BelongsTo
    {
        return $this->belongsTo(User::class, 'agent_id');
    }

    /**
     * Get the driver assigned to the shipment.
     */
    public function driver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'driver_id');
    }

    /**
     * Get the vehicle assigned to the shipment.
     */
    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    /**
     * Get the pickup address.
     */
    public function pickupAddress(): BelongsTo
    {
        return $this->belongsTo(Address::class, 'pickup_address_id');
    }

    /**
     * Get the delivery address.
     */
    public function deliveryAddress(): BelongsTo
    {
        return $this->belongsTo(Address::class, 'delivery_address_id');
    }

    /**
     * Get the tracking history for the shipment.
     */
    public function trackingHistory(): HasMany
    {
        return $this->hasMany(ShipmentTracking::class)->orderBy('created_at');
    }

    /**
     * Get the transactions related to this shipment.
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    /**
     * Get the invoice for this shipment.
     */
    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    /**
     * Get the latest tracking point.
     */
    public function getLatestTrackingAttribute(): ?ShipmentTracking
    {
        return $this->trackingHistory()->latest()->first();
    }

    /**
     * Get the shipment's status label.
     */
    public function getStatusLabelAttribute(): string
    {
        return str_replace('_', ' ', title_case($this->status));
    }

    /**
     * Get the shipment's priority label.
     */
    public function getPriorityLabelAttribute(): string
    {
        return str_replace('_', ' ', title_case($this->priority));
    }

    /**
     * Get the shipment's type label.
     */
    public function getShipmentTypeLabelAttribute(): string
    {
        return str_replace('_', ' ', title_case($this->shipment_type));
    }

    /**
     * Get the total dimensions.
     */
    public function getTotalDimensionsAttribute(): string
    {
        return "{$this->dimensions_length} × {$this->dimensions_width} × {$this->dimensions_height} cm";
    }

    /**
     * Get the estimated delivery time.
     */
    public function getEstimatedDeliveryTimeAttribute(): ?\Carbon\Carbon
    {
        if (!$this->pickup_completed_at || !$this->estimated_duration) {
            return null;
        }

        return $this->pickup_completed_at->addMinutes($this->estimated_duration);
    }

    /**
     * Check if shipment is delivered.
     */
    public function isDelivered(): bool
    {
        return $this->status === 'delivered';
    }

    /**
     * Check if shipment is in transit.
     */
    public function isInTransit(): bool
    {
        return in_array($this->status, ['picked_up', 'in_transit', 'out_for_delivery']);
    }

    /**
     * Check if shipment is pending pickup.
     */
    public function isPendingPickup(): bool
    {
        return in_array($this->status, ['confirmed', 'assigned', 'pickup_scheduled']);
    }

    /**
     * Check if shipment can be cancelled.
     */
    public function canBeCancelled(): bool
    {
        return !in_array($this->status, ['delivered', 'cancelled', 'returned']);
    }

    /**
     * Update shipment status and add tracking point.
     */
    public function updateStatus(string $status, array $trackingData = []): void
    {
        $this->status = $status;
        $this->save();

        // Add tracking point
        $this->trackingHistory()->create(array_merge([
            'status' => $status,
            'status_description' => $this->getStatusLabelAttribute(),
            'user_id' => auth()->id(),
            'visible_to_customer' => true,
        ], $trackingData));
    }

    /**
     * Assign driver and vehicle to shipment.
     */
    public function assignDriverAndVehicle(User $driver, Vehicle $vehicle): void
    {
        $this->driver()->associate($driver);
        $this->vehicle()->associate($vehicle);
        $this->updateStatus('assigned');
    }

    /**
     * Calculate estimated cost based on distance and weight.
     */
    public static function calculateCost(float $distance, float $weight, string $shipmentType = 'standard'): array
    {
        $baseRate = config('logistics.base_rates.' . $shipmentType, 5.00);
        $weightRate = config('logistics.weight_rate', 0.50);
        $distanceRate = config('logistics.distance_rate', 2.00);

        $shippingCost = ($baseRate + ($weight * $weightRate) + ($distance * $distanceRate));
        $insuranceCost = $shippingCost * 0.05; // 5% insurance
        $totalCost = $shippingCost + $insuranceCost;

        return [
            'shipping_cost' => round($shippingCost, 2),
            'insurance_cost' => round($insuranceCost, 2),
            'total_cost' => round($totalCost, 2),
        ];
    }

    /**
     * Scope to get shipments by status.
     */
    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope to get shipments by customer.
     */
    public function scopeByCustomer($query, int $customerId)
    {
        return $query->where('customer_id', $customerId);
    }

    /**
     * Scope to get shipments by driver.
     */
    public function scopeByDriver($query, int $driverId)
    {
        return $query->where('driver_id', $driverId);
    }

    /**
     * Scope to get pending shipments.
     */
    public function scopePending($query)
    {
        return $query->whereIn('status', ['confirmed', 'assigned', 'pickup_scheduled']);
    }

    /**
     * Scope to get active shipments.
     */
    public function scopeActive($query)
    {
        return $query->whereIn('status', ['picked_up', 'in_transit', 'out_for_delivery']);
    }

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        // Generate tracking number on creation
        static::creating(function ($shipment) {
            if (!$shipment->tracking_number) {
                $shipment->tracking_number = 'LH' . date('Y') . str_pad(mt_rand(1, 999999), 6, '0', STR_PAD_LEFT);
            }
        });

        // Create initial tracking point
        static::created(function ($shipment) {
            $shipment->trackingHistory()->create([
                'status' => 'created',
                'status_description' => 'Shipment created',
                'user_id' => $shipment->customer_id,
                'visible_to_customer' => true,
            ]);
        });
    }
}