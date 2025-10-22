<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;

class ShipmentTracking extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'shipment_id',
        'user_id',
        'status',
        'status_description',
        'latitude',
        'longitude',
        'location_name',
        'city',
        'state',
        'country',
        'notes',
        'metadata',
        'visible_to_customer',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'metadata' => 'array',
        'visible_to_customer' => 'boolean',
    ];

    /**
     * Get the shipment being tracked.
     */
    public function shipment(): BelongsTo
    {
        return $this->belongsTo(Shipment::class);
    }

    /**
     * Get the user who created this tracking point.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the formatted location.
     */
    public function getFormattedLocationAttribute(): string
    {
        $parts = array_filter([
            $this->location_name,
            $this->city,
            $this->state,
            $this->country,
        ]);

        return implode(', ', $parts) ?: 'Unknown Location';
    }

    /**
     * Get the coordinates as an array.
     */
    public function getCoordinatesAttribute(): ?array
    {
        if ($this->latitude && $this->longitude) {
            return [
                'latitude' => (float) $this->latitude,
                'longitude' => (float) $this->longitude,
            ];
        }

        return null;
    }

    /**
     * Create a new tracking point.
     */
    public static function createTrackingPoint(
        Shipment $shipment,
        string $status,
        array $data = [],
        ?User $user = null
    ): self {
        return static::create(array_merge([
            'shipment_id' => $shipment->id,
            'user_id' => $user?->id,
            'status' => $status,
            'status_description' => $data['status_description'] ?? str_replace('_', ' ', title_case($status)),
            'visible_to_customer' => $data['visible_to_customer'] ?? true,
        ], $data));
    }

    /**
     * Scope to get visible to customer tracking points.
     */
    public function scopeVisibleToCustomer($query)
    {
        return $query->where('visible_to_customer', true);
    }

    /**
     * Scope to get tracking points with location.
     */
    public function scopeWithLocation($query)
    {
        return $query->whereNotNull('latitude')
                    ->whereNotNull('longitude');
    }
}