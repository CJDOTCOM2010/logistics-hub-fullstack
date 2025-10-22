<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\DB;

class RoleAndPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Clear existing roles and permissions
        DB::table('model_has_permissions')->delete();
        DB::table('model_has_roles')->delete();
        DB::table('role_has_permissions')->delete();
        Role::query()->delete();
        Permission::query()->delete();

        // Define permissions
        $permissions = [
            // Core system permissions
            'access-admin-panel',
            'manage-system-settings',
            'view-system-logs',
            'manage-users',
            'manage-roles',
            'manage-permissions',

            // User management
            'view-users',
            'create-users',
            'edit-users',
            'delete-users',
            'verify-kyc',
            'suspend-users',
            'manage-user-profiles',

            // Shipment management
            'view-shipments',
            'create-shipments',
            'edit-shipments',
            'delete-shipments',
            'assign-drivers',
            'track-shipments',
            'cancel-shipments',
            'view-shipment-reports',

            // Vehicle management
            'view-vehicles',
            'create-vehicles',
            'edit-vehicles',
            'delete-vehicles',
            'assign-vehicles',
            'manage-vehicle-maintenance',
            'view-vehicle-tracking',

            // Driver management
            'view-drivers',
            'assign-drivers-to-shipments',
            'manage-driver-schedules',
            'view-driver-performance',
            'manage-driver-payments',

            // Customer management
            'view-customers',
            'manage-customers',
            'view-customer-orders',
            'manage-customer-accounts',

            // Agent management
            'view-agents',
            'manage-agents',
            'assign-agents-to-shipments',
            'view-agent-performance',
            'manage-agent-commissions',

            // Financial management
            'view-transactions',
            'manage-payments',
            'process-refunds',
            'view-financial-reports',
            'manage-invoices',
            'manage-pricing',
            'manage-wallets',

            // HR management
            'view-employees',
            'manage-employees',
            'manage-departments',
            'manage-positions',
            'view-payroll',
            'manage-payroll',
            'view-attendance',
            'manage-leave',

            // Notifications and messaging
            'send-notifications',
            'manage-notifications',
            'view-conversations',
            'manage-conversations',
            'send-bulk-emails',
            'manage-sms-templates',

            // Reports and analytics
            'view-dashboard',
            'view-reports',
            'export-reports',
            'view-analytics',
            'manage-exports',

            // Content management
            'manage-pages',
            'manage-blog',
            'manage-testimonials',
            'manage-banners',
            'manage-seo-settings',

            // API access
            'access-api',
            'manage-api-keys',
            'view-api-logs',

            // Support and helpdesk
            'view-support-tickets',
            'manage-support-tickets',
            'view-faqs',
            'manage-faqs',

            // Backup and maintenance
            'manage-backups',
            'run-maintenance',
            'view-activity-logs',
        ];

        // Create permissions
        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }

        // Define roles with their permissions
        $roles = [
            'super_admin' => [
                // Super admin has all permissions
                ...$permissions
            ],

            'admin' => [
                'access-admin-panel',
                'view-users',
                'create-users',
                'edit-users',
                'verify-kyc',
                'suspend-users',
                'view-shipments',
                'create-shipments',
                'edit-shipments',
                'assign-drivers',
                'track-shipments',
                'view-vehicles',
                'create-vehicles',
                'edit-vehicles',
                'assign-vehicles',
                'view-drivers',
                'assign-drivers-to-shipments',
                'manage-driver-schedules',
                'view-customers',
                'manage-customers',
                'view-agents',
                'manage-agents',
                'assign-agents-to-shipments',
                'view-transactions',
                'manage-payments',
                'process-refunds',
                'view-financial-reports',
                'manage-invoices',
                'view-employees',
                'manage-employees',
                'manage-departments',
                'manage-positions',
                'send-notifications',
                'view-conversations',
                'view-dashboard',
                'view-reports',
                'export-reports',
                'view-analytics',
                'manage-pages',
                'manage-blog',
                'view-support-tickets',
                'manage-support-tickets',
                'view-activity-logs',
            ],

            'accountant' => [
                'access-admin-panel',
                'view-transactions',
                'manage-payments',
                'process-refunds',
                'view-financial-reports',
                'manage-invoices',
                'manage-pricing',
                'manage-wallets',
                'view-dashboard',
                'view-reports',
                'export-reports',
                'view-shipments',
                'view-customers',
                'view-agents',
                'view-drivers',
            ],

            'hr' => [
                'access-admin-panel',
                'view-users',
                'create-users',
                'edit-users',
                'view-employees',
                'manage-employees',
                'manage-departments',
                'manage-positions',
                'view-payroll',
                'manage-payroll',
                'view-attendance',
                'manage-leave',
                'view-dashboard',
                'view-reports',
                'export-reports',
                'send-notifications',
            ],

            'driver' => [
                'view-own-shipments',
                'update-shipment-status',
                'update-own-location',
                'view-own-profile',
                'edit-own-profile',
                'view-own-earnings',
                'view-own-schedule',
                'send-notifications',
                'view-conversations',
                'access-api',
            ],

            'agent' => [
                'view-assigned-shipments',
                'manage-assigned-shipments',
                'create-shipments',
                'edit-own-shipments',
                'view-own-profile',
                'edit-own-profile',
                'view-customers',
                'manage-customers',
                'view-own-earnings',
                'view-conversations',
                'send-notifications',
                'access-api',
            ],

            'customer' => [
                'create-shipments',
                'view-own-shipments',
                'track-own-shipments',
                'cancel-own-shipments',
                'view-own-profile',
                'edit-own-profile',
                'manage-own-addresses',
                'view-own-transactions',
                'manage-wallet',
                'view-conversations',
                'access-api',
            ],
        ];

        // Create roles and assign permissions
        foreach ($roles as $roleName => $rolePermissions) {
            $role = Role::create(['name' => $roleName]);
            $role->givePermissionTo($rolePermissions);
        }

        $this->command->info('Roles and permissions created successfully!');
    }
}