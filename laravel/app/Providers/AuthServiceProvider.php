<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\Models\User;
use App\Models\Shipment;
use App\Models\Vehicle;
use App\Models\Transaction;
use App\Policies\ShipmentPolicy;
use App\Policies\VehiclePolicy;
use App\Policies\TransactionPolicy;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Shipment::class => ShipmentPolicy::class,
        Vehicle::class => VehiclePolicy::class,
        Transaction::class => TransactionPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        // Implicitly grant "Super Admin" role all permissions
        Gate::before(function (User $user, string $ability) {
            if ($user->hasRole('super_admin')) {
                return true;
            }
        });

        // Define gates for common operations
        Gate::define('access-admin-panel', function (User $user) {
            return $user->hasAnyRole(['super_admin', 'admin', 'accountant', 'hr']);
        });

        Gate::define('manage-own-shipments', function (User $user, Shipment $shipment) {
            return $user->id === $shipment->customer_id ||
                   $user->id === $shipment->agent_id ||
                   $user->id === $shipment->driver_id;
        });

        Gate::define('manage-own-vehicle', function (User $user, Vehicle $vehicle) {
            return $user->id === $vehicle->driver_id ||
                   $user->id === $vehicle->owner_id;
        });

        Gate::define('view-own-profile', function (User $user, User $profileUser) {
            return $user->id === $profileUser->id;
        });

        Gate::define('edit-own-profile', function (User $user, User $profileUser) {
            return $user->id === $profileUser->id;
        });

        // Customer specific gates
        Gate::define('create-customer-shipment', function (User $user) {
            return $user->isCustomer() && $user->status === 'active';
        });

        // Driver specific gates
        Gate::define('accept-shipment', function (User $user, Shipment $shipment) {
            return $user->isDriver() &&
                   $user->status === 'active' &&
                   $shipment->status === 'assigned' &&
                   $shipment->driver_id === $user->id;
        });

        Gate::define('update-shipment-location', function (User $user) {
            return $user->isDriver() && $user->status === 'active';
        });

        // Agent specific gates
        Gate::define('manage-agent-shipments', function (User $user) {
            return $user->isAgent() && $user->status === 'active';
        });

        // Admin specific gates
        Gate::define('manage-users', function (User $user) {
            return $user->hasAnyRole(['super_admin', 'admin']);
        });

        Gate::define('verify-kyc', function (User $user) {
            return $user->hasAnyRole(['super_admin', 'admin']);
        });

        Gate::define('manage-system-settings', function (User $user) {
            return $user->hasRole('super_admin');
        });
    }
}