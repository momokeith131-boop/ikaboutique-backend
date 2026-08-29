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

// API v1 routes
Route::prefix('v1')->group(function () {

    // ============================================
    // PUBLIC ROUTES (No Authentication Required)
    // ============================================

    // Authentication
    Route::post('/auth/register', [AuthController::class, 'register']);
    Route::post('/auth/login', [AuthController::class, 'login']);

    // Products (Public listing)
    Route::get('/products', [ProductController::class, 'index']);
    Route::get('/products/{id}', [ProductController::class, 'show']);

    // Categories
    Route::get('/categories', [CategoryController::class, 'index']);
Route::get('/categories/{id}', [CategoryController::class, 'show']);
Route::post('/categories', [CategoryController::class, 'store']);

 // Ajouté // Shops (Public listing)
    Route::get('/shops', [ShopController::class, 'index']);
    Route::get('/shops/{id}', [ShopController::class, 'show']);
    Route::post('/shops', [ShopController::class, 'store']); 

    // ============================================
    // PROTECTED ROUTES (Authentication Required)
    // ============================================
    Route::middleware('auth:api')->group(function () {

        // ---- Authentication ----
        Route::post('/auth/logout', [AuthController::class, 'logout']);
        Route::get('/auth/me', [AuthController::class, 'me']);
        Route::post('/auth/refresh', [AuthController::class, 'refresh']);

        // ---- Products (Seller Routes) ----
        Route::post('/products', [ProductController::class, 'store']);
        Route::put('/products/{id}', [ProductController::class, 'update']);
        Route::delete('/products/{id}', [ProductController::class, 'destroy']);

        // ---- Shops ----
        Route::post('/shops', [ShopController::class, 'store']);
        Route::put('/shops/{id}', [ShopController::class, 'update']);
        Route::get('/my-shop', [ShopController::class, 'myShop']);

        // ---- Cart ----
        Route::get('/cart', [CartController::class, 'index']);
        Route::post('/cart/add', [CartController::class, 'add']);
        Route::put('/cart/items/{itemId}', [CartController::class, 'update']);
        Route::delete('/cart/items/{itemId}', [CartController::class, 'remove']);
        Route::delete('/cart/clear', [CartController::class, 'clear']);

        // ---- Orders ----
        Route::get('/orders', [OrderController::class, 'index']);
        Route::get('/orders/{id}', [OrderController::class, 'show']);
        Route::post('/orders', [OrderController::class, 'store']);
        Route::put('/orders/{id}/status', [OrderController::class, 'updateStatus']);

        // ---- Payments ----
        Route::post('/payments/initiate', [PaymentController::class, 'initiate']);
        Route::post('/payments/{id}/confirm', [PaymentController::class, 'confirm']);
        Route::get('/payments/{id}/status', [PaymentController::class, 'getStatus']);
	// ---- Product Images ----
	Route::post('/products/{id}/images', [ProductController::class, 'addImage']);
	Route::delete('/products/images/{id}', [ProductController::class, 'deleteImage']);
	Route::get('/products/{id}/images', [ProductController::class, 'getImages']);

        // ---- Notifications ----
                // ---- Notifications ----
        Route::get('/notifications', [NotificationController::class, 'index']);
        Route::put('/notifications/{id}/read', [NotificationController::class, 'markAsRead']);
        Route::post('/notifications/subscribe', [NotificationController::class, 'subscribe']);
        Route::post('/notifications/unsubscribe', [NotificationController::class, 'unsubscribe']);
        Route::post('/notifications/create', [NotificationController::class, 'createNotification']); 
 });
});
