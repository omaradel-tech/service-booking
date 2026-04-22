<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

Route::prefix('v1')->group(function () {
    // Authentication routes (with auth rate limiting)
    Route::middleware('throttle:auth')->group(function () {
        Route::post('/auth/register', [\App\Modules\User\Controllers\AuthController::class, 'register']);
        Route::post('/auth/login', [\App\Modules\User\Controllers\AuthController::class, 'login']);
    });
    Route::post('/auth/logout', [\App\Modules\User\Controllers\AuthController::class, 'logout'])->middleware('auth:sanctum');
    Route::get('/auth/me', [\App\Modules\User\Controllers\AuthController::class, 'me'])->middleware('auth:sanctum');

    // Service routes (public)
    Route::get('/services', [\App\Modules\Service\Controllers\ServiceController::class, 'index']);
    Route::get('/services/{id}', [\App\Modules\Service\Controllers\ServiceController::class, 'show']);

    // Package routes (public)
    Route::get('/packages', [\App\Modules\Package\Controllers\PackageController::class, 'index']);
    Route::get('/packages/{id}', [\App\Modules\Package\Controllers\PackageController::class, 'show']);

    // Protected routes (require authentication)
    Route::middleware('auth:sanctum', 'throttle:api')->group(function () {
        // Booking routes
        Route::get('/bookings', [\App\Modules\Booking\Controllers\BookingController::class, 'index']);
        Route::post('/bookings', [\App\Modules\Booking\Controllers\BookingController::class, 'store'])->middleware('throttle:booking', 'idempotent');
        Route::get('/bookings/{id}', [\App\Modules\Booking\Controllers\BookingController::class, 'show']);
        Route::patch('/bookings/{id}/cancel', [\App\Modules\Booking\Controllers\BookingController::class, 'cancel']);

        // Cart routes
        Route::get('/cart', [\App\Modules\Cart\Controllers\CartController::class, 'index']);
        Route::post('/cart/items', [\App\Modules\Cart\Controllers\CartController::class, 'addItem']);
        Route::patch('/cart/items/{id}', [\App\Modules\Cart\Controllers\CartController::class, 'updateItem']);
        Route::delete('/cart/items/{id}', [\App\Modules\Cart\Controllers\CartController::class, 'removeItem']);

        // Checkout route
        Route::post('/checkout', [\App\Modules\Cart\Controllers\CheckoutController::class, 'checkout'])->middleware('throttle:checkout', 'idempotent');

        // Subscription routes
        Route::get('/subscriptions/current', [\App\Modules\Subscription\Controllers\SubscriptionController::class, 'current']);
        Route::post('/subscriptions/start-trial', [\App\Modules\Subscription\Controllers\SubscriptionController::class, 'startTrial'])->middleware('idempotent');
        Route::post('/subscriptions/cancel', [\App\Modules\Subscription\Controllers\SubscriptionController::class, 'cancel'])->middleware('idempotent');
        Route::get('/subscriptions/check', [\App\Modules\Subscription\Controllers\SubscriptionController::class, 'check']);
    });
});
