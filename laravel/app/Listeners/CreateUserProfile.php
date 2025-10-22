<?php

namespace App\Listeners;

use App\Events\UserRegistered;
use App\Models\UserProfile;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Str;

class CreateUserProfile
{
    use InteractsWithQueue;

    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(UserRegistered $event): void
    {
        UserProfile::create([
            'user_id' => $event->user->id,
            'wallet_balance' => 0.00,
            'credit_limit' => 0.00,
            'referral_code' => strtoupper(Str::random(8)),
            'notification_settings' => [
                'email' => true,
                'sms' => false,
                'push' => true,
                'shipment_updates' => true,
                'payment_notifications' => true,
                'marketing_emails' => false,
            ],
            'preferences' => [
                'language' => 'en',
                'timezone' => 'UTC',
                'currency' => 'USD',
            ],
        ]);
    }
}