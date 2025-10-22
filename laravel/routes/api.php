<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\UserController;
use App\Http\Controllers\API\ShipmentController;
use App\Http\Controllers\API\VehicleController;
use App\Http\Controllers\API\LocationController;
use App\Http\Controllers\API\PaymentController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Public routes
Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/login', [AuthController::class, 'login']);

// Shipment tracking (public)
Route::get('/track/{trackingNumber}', [ShipmentController::class, 'track']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {

    // Authentication routes
    Route::prefix('auth')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::post('/logout-all', [AuthController::class, 'logoutAll']);
        Route::get('/user', [AuthController::class, 'user']);
        Route::post('/refresh', [AuthController::class, 'refresh']);
        Route::post('/change-password', [AuthController::class, 'changePassword']);
        Route::get('/tokens', [AuthController::class, 'tokens']);
        Route::delete('/tokens/{tokenId}', [AuthController::class, 'revokeToken']);
    });

    // User management
    Route::prefix('users')->group(function () {
        Route::get('/profile', [UserController::class, 'profile']);
        Route::put('/profile', [UserController::class, 'updateProfile']);
        Route::post('/avatar', [UserController::class, 'uploadAvatar']);
        Route::get('/addresses', [UserController::class, 'addresses']);
        Route::post('/addresses', [UserController::class, 'addAddress']);
        Route::put('/addresses/{address}', [UserController::class, 'updateAddress']);
        Route::delete('/addresses/{address}', [UserController::class, 'deleteAddress']);
        Route::get('/notifications', [UserController::class, 'notifications']);
        Route::put('/notifications/{notification}/read', [UserController::class, 'markNotificationRead']);
    });

    // Shipments
    Route::prefix('shipments')->group(function () {
        Route::get('/', [ShipmentController::class, 'index']);
        Route::post('/', [ShipmentController::class, 'store']);
        Route::get('/{shipment}', [ShipmentController::class, 'show']);
        Route::put('/{shipment}', [ShipmentController::class, 'update']);
        Route::delete('/{shipment}', [ShipmentController::class, 'destroy']);
        Route::get('/{shipment}/tracking', [ShipmentController::class, 'tracking']);
        Route::post('/{shipment}/cancel', [ShipmentController::class, 'cancel']);
        Route::post('/{shipment}/tracking/update', [ShipmentController::class, 'addTrackingPoint']);
    });

    // Vehicles (for drivers and admins)
    Route::prefix('vehicles')->group(function () {
        Route::get('/', [VehicleController::class, 'index']);
        Route::get('/{vehicle}', [VehicleController::class, 'show']);
        Route::post('/{vehicle}/location', [VehicleController::class, 'updateLocation']);
        Route::get('/{vehicle}/history', [VehicleController::class, 'locationHistory']);
    });

    // Location services
    Route::prefix('locations')->group(function () {
        Route::get('/nearby', [LocationController::class, 'nearby']);
        Route::get('/distance', [LocationController::class, 'calculateDistance']);
        Route::get('/route', [LocationController::class, 'getRoute']);
    });

    // Payment processing
    Route::prefix('payments')->group(function () {
        Route::get('/methods', [PaymentController::class, 'getPaymentMethods']);
        Route::post('/process', [PaymentController::class, 'processPayment']);
        Route::get('/transactions', [PaymentController::class, 'transactions']);
        Route::get('/wallet/balance', [PaymentController::class, 'getWalletBalance']);
        Route::post('/wallet/topup', [PaymentController::class, 'topupWallet']);
    });

    // Role-based routes (for specific user types)
    Route::middleware(['role:driver'])->prefix('driver')->group(function () {
        Route::get('/shipments/assigned', [ShipmentController::class, 'assignedShipments']);
        Route::post('/shipments/{shipment}/pickup', [ShipmentController::class, 'markPickedUp']);
        Route::post('/shipments/{shipment}/deliver', [ShipmentController::class, 'markDelivered']);
        Route::get('/earnings', [PaymentController::class, 'driverEarnings']);
    });

    Route::middleware(['role:agent'])->prefix('agent')->group(function () {
        Route::get('/shipments/managed', [ShipmentController::class, 'managedShipments']);
        Route::post('/shipments/{shipment}/assign-driver', [ShipmentController::class, 'assignDriver']);
    });

    Route::middleware(['role:admin|super_admin'])->prefix('admin')->group(function () {
        Route::get('/users', [UserController::class, 'index']);
        Route::post('/users/{user}/verify', [UserController::class, 'verifyUser']);
        Route::post('/users/{user}/suspend', [UserController::class, 'suspendUser']);
        Route::get('/vehicles/all', [VehicleController::class, 'allVehicles']);
        Route::post('/vehicles', [VehicleController::class, 'store']);
        Route::put('/vehicles/{vehicle}', [VehicleController::class, 'update']);
        Route::delete('/vehicles/{vehicle}', [VehicleController::class, 'destroy']);
    });

    // Chat/Messaging
    Route::prefix('conversations')->group(function () {
        Route::get('/', [ConversationController::class, 'index']);
        Route::post('/', [ConversationController::class, 'store']);
        Route::get('/{conversation}', [ConversationController::class, 'show']);
        Route::post('/{conversation}/messages', [MessageController::class, 'store']);
        Route::put('/messages/{message}/read', [MessageController::class, 'markRead']);
    });
});

// Webhook routes (for payment gateways, etc.)
Route::prefix('webhooks')->group(function () {
    Route::post('/stripe', [PaymentController::class, 'stripeWebhook']);
    Route::post('/paypal', [PaymentController::class, 'paypalWebhook']);
    Route::post('/paystack', [PaymentController::class, 'paystackWebhook']);
    Route::post('/flutterwave', [PaymentController::class, 'flutterwaveWebhook']);
});