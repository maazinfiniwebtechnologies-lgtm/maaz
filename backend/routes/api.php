<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\CommissionController;
use App\Http\Controllers\Api\WalletController;
use App\Http\Controllers\Api\WithdrawalController;

Route::prefix('v1')->group(function () {
    // Public routes
    Route::post('/auth/register', [AuthController::class, 'register']);
    Route::post('/auth/login', [AuthController::class, 'login']);

    // Public product routes
    Route::get('/products', [ProductController::class, 'index']);
    Route::get('/products/featured', [ProductController::class, 'featured']);
    Route::get('/products/{product}', [ProductController::class, 'show']);

    // Protected routes
    Route::middleware('auth:sanctum')->group(function () {
        // Auth routes
        Route::post('/auth/logout', [AuthController::class, 'logout']);
        Route::get('/auth/me', [AuthController::class, 'me']);

        // User routes
        Route::get('/users', [UserController::class, 'index']);
        Route::get('/users/{user}', [UserController::class, 'show']);
        Route::put('/users/{user}', [UserController::class, 'update']);
        Route::get('/users/{user}/team/hierarchy', [UserController::class, 'getTeamHierarchy']);
        Route::get('/users/{user}/team/stats', [UserController::class, 'getTeamStats']);
        Route::get('/users/{user}/downlines', [UserController::class, 'getDownlines']);

        // Order routes
        Route::get('/orders', [OrderController::class, 'userOrders']);
        Route::post('/orders', [OrderController::class, 'store']);
        Route::get('/orders/{order}', [OrderController::class, 'show']);
        Route::put('/orders/{order}/cancel', [OrderController::class, 'cancel']);

        // Commission routes
        Route::get('/commissions', [CommissionController::class, 'userCommissions']);
        Route::get('/commissions/total', [CommissionController::class, 'userTotal']);
        Route::get('/commissions/{commission}', [CommissionController::class, 'show']);

        // Wallet routes
        Route::get('/wallet', [WalletController::class, 'show']);
        Route::get('/wallet/balance', [WalletController::class, 'balance']);
        Route::get('/wallet/transactions', [WalletController::class, 'transactions']);

        // Withdrawal routes
        Route::get('/withdrawals', [WithdrawalController::class, 'userWithdrawals']);
        Route::post('/withdrawals', [WithdrawalController::class, 'store']);
        Route::get('/withdrawals/{withdrawal}', [WithdrawalController::class, 'show']);

        // Admin routes
        Route::middleware('admin')->group(function () {
            // Product management
            Route::post('/products', [ProductController::class, 'store']);
            Route::put('/products/{product}', [ProductController::class, 'update']);
            Route::delete('/products/{product}', [ProductController::class, 'destroy']);

            // Order management
            Route::get('/admin/orders', [OrderController::class, 'index']);
            Route::put('/orders/{order}/mark-as-paid', [OrderController::class, 'markAsPaid']);
            Route::put('/orders/{order}/mark-as-shipped', [OrderController::class, 'markAsShipped']);
            Route::put('/orders/{order}/mark-as-delivered', [OrderController::class, 'markAsDelivered']);

            // Commission management
            Route::get('/admin/commissions', [CommissionController::class, 'index']);
            Route::put('/commissions/{commission}/approve', [CommissionController::class, 'approve']);
            Route::put('/commissions/{commission}/reject', [CommissionController::class, 'reject']);

            // Wallet management
            Route::put('/wallet/{user}/freeze', [WalletController::class, 'freeze']);
            Route::put('/wallet/{user}/unfreeze', [WalletController::class, 'unfreeze']);

            // Withdrawal management
            Route::get('/admin/withdrawals', [WithdrawalController::class, 'index']);
            Route::put('/withdrawals/{withdrawal}/approve', [WithdrawalController::class, 'approve']);
            Route::put('/withdrawals/{withdrawal}/reject', [WithdrawalController::class, 'reject']);
            Route::put('/withdrawals/{withdrawal}/mark-as-processed', [WithdrawalController::class, 'markAsProcessed']);
        });
    });
});
