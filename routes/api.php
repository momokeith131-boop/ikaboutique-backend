<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\ShopController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\SubscriptionController;
use App\Http\Controllers\Api\AdminController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\StatisticController;
use App\Http\Controllers\Api\AccountingController;
use App\Http\Controllers\Api\DomainController;
use App\Http\Controllers\Api\BackupController;

Route::prefix('v1')->group(function () {

    Route::post('/auth/register', [AuthController::class, 'register']);
    Route::post('/auth/login', [AuthController::class, 'login']);

    Route::get('/products', [ProductController::class, 'index']);
    Route::get('/products/{id}', [ProductController::class, 'show']);

    Route::get('/categories', [CategoryController::class, 'index']);
    Route::get('/categories/{id}', [CategoryController::class, 'show']);

    Route::get('/shops', [ShopController::class, 'index']);
    Route::get('/shops/{id}', [ShopController::class, 'show']);

    Route::get('/sellers', [UserController::class, 'sellers']);

    Route::middleware('auth:api')->group(function () {

        Route::post('/auth/logout', [AuthController::class, 'logout']);
        Route::get('/auth/me', [AuthController::class, 'me']);
        Route::post('/auth/refresh', [AuthController::class, 'refresh']);

        Route::post('/products', [ProductController::class, 'store']);
        Route::put('/products/{id}', [ProductController::class, 'update']);
        Route::delete('/products/{id}', [ProductController::class, 'destroy']);
        Route::post('/products/{id}/images', [ProductController::class, 'addImage']);
        Route::delete('/products/images/{id}', [ProductController::class, 'deleteImage']);
        Route::get('/products/{id}/images', [ProductController::class, 'getImages']);
        Route::put('/products/{id}/stock', [ProductController::class, 'updateStock']);
        Route::post('/products/{id}/restock', [ProductController::class, 'restock']);
        Route::get('/products/low-stock', [ProductController::class, 'lowStock']);
        Route::get('/products/{id}/stock-history', [ProductController::class, 'stockHistory']);

        Route::post('/shops', [ShopController::class, 'store']);
        Route::put('/shops/{id}', [ShopController::class, 'update']);
        Route::get('/my-shop', [ShopController::class, 'myShop']);

        Route::get('/cart', [CartController::class, 'index']);
        Route::post('/cart/add', [CartController::class, 'add']);
        Route::put('/cart/items/{itemId}', [CartController::class, 'update']);
        Route::delete('/cart/items/{itemId}', [CartController::class, 'remove']);
        Route::delete('/cart/clear', [CartController::class, 'clear']);

        Route::get('/orders', [OrderController::class, 'index']);
        Route::get('/orders/{id}', [OrderController::class, 'show']);
        Route::post('/orders', [OrderController::class, 'store']);
        Route::put('/orders/{id}/status', [OrderController::class, 'updateStatus']);

        Route::post('/payments/initiate', [PaymentController::class, 'initiate']);
        Route::post('/payments/{id}/confirm', [PaymentController::class, 'confirm']);
        Route::get('/payments/{id}/status', [PaymentController::class, 'getStatus']);

        Route::get('/notifications', [NotificationController::class, 'index']);
        Route::put('/notifications/{id}/read', [NotificationController::class, 'markAsRead']);
        Route::post('/notifications/subscribe', [NotificationController::class, 'subscribe']);
        Route::post('/notifications/unsubscribe', [NotificationController::class, 'unsubscribe']);
        Route::post('/notifications/create', [NotificationController::class, 'createNotification']);

        Route::get('/subscription/plans', [SubscriptionController::class, 'plans']);
        Route::get('/subscription/current', [SubscriptionController::class, 'current']);
        Route::post('/subscription/subscribe', [SubscriptionController::class, 'subscribe']);
        Route::post('/subscription/cancel', [SubscriptionController::class, 'cancel']);
        Route::get('/subscription/limits', [SubscriptionController::class, 'checkLimits']);
        Route::get('/subscription/countdown', [SubscriptionController::class, 'countdown']);

        Route::post('/stats/track', [StatisticController::class, 'track']);
        Route::get('/stats/shop/{shopId}', [StatisticController::class, 'shopStats']);
        Route::get('/stats/global', [StatisticController::class, 'globalStats']);

        Route::get('/accounting/summary/{shopId}', [AccountingController::class, 'summary']);
        Route::get('/accounting/transactions/{shopId}', [AccountingController::class, 'transactions']);
        Route::post('/accounting/transactions', [AccountingController::class, 'store']);
        Route::put('/accounting/transactions/{id}', [AccountingController::class, 'update']);
        Route::delete('/accounting/transactions/{id}', [AccountingController::class, 'destroy']);

        Route::get('/domains', [DomainController::class, 'index']);
        Route::post('/domains', [DomainController::class, 'store']);
        Route::get('/domains/{id}', [DomainController::class, 'show']);
        Route::put('/domains/{id}', [DomainController::class, 'update']);
        Route::delete('/domains/{id}', [DomainController::class, 'destroy']);
        Route::post('/domains/{id}/verify', [DomainController::class, 'verify']);
        Route::post('/domains/{id}/set-primary', [DomainController::class, 'setPrimary']);

        Route::get('/backups', [BackupController::class, 'index']);
        Route::post('/backups', [BackupController::class, 'store']);
        Route::get('/backups/{id}', [BackupController::class, 'show']);
        Route::delete('/backups/{id}', [BackupController::class, 'destroy']);
        Route::get('/backups/{id}/download', [BackupController::class, 'download']);

        Route::middleware(['role:admin'])->prefix('admin')->group(function () {
            Route::get('/dashboard', [AdminController::class, 'dashboard']);
            Route::get('/users', [AdminController::class, 'users']);
            Route::get('/users/{id}', [AdminController::class, 'showUser']);
            Route::put('/users/{id}', [AdminController::class, 'updateUser']);
            Route::post('/users/{id}/suspend', [AdminController::class, 'suspendMerchant']);
            Route::post('/users/{id}/reactivate', [AdminController::class, 'reactivateMerchant']);
            Route::get('/shops', [AdminController::class, 'shops']);
            Route::get('/subscriptions', [AdminController::class, 'subscriptions']);
            Route::get('/payments', [AdminController::class, 'payments']);
            Route::get('/stats', [AdminController::class, 'stats']);
        });
    });
});
