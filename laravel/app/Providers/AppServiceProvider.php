<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Set default string length for older MySQL versions
        Schema::defaultStringLength(191);

        // Custom validation rules
        Validator::extend('phone_number', function ($attribute, $value, $parameters, $validator) {
            return preg_match('/^[+]?[1-9]\d{1,14}$/', $value);
        });

        Validator::extend('tracking_number', function ($attribute, $value, $parameters, $validator) {
            return preg_match('/^[A-Z]{2}\d{8}$/', $value);
        });

        // Custom validation messages
        Validator::replacer('phone_number', function ($message, $attribute, $rule, $parameters) {
            return str_replace(':attribute', $attribute, 'The :attribute must be a valid phone number.');
        });

        Validator::replacer('tracking_number', function ($message, $attribute, $rule, $parameters) {
            return str_replace(':attribute', $attribute, 'The :attribute must be a valid tracking number format.');
        });
    }
}