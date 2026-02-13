<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\MaterialController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProductImageController;
use App\Http\Controllers\ProductSizeImageController;
use Illuminate\Support\Facades\Route;

// Login
Route::post('login', [AuthController::class, 'login']);

// Protected Routes (Login Required)
Route::middleware('auth:sanctum')->group(function () {
    // CATEGORY ROUTES
    Route::prefix('categories')->group(function () {
        Route::get('/', [CategoryController::class, 'index']);
        Route::post('/store', [CategoryController::class, 'store']);
        Route::patch('/{id}', [CategoryController::class, 'update']);
        Route::delete('/delete/{id}', [CategoryController::class, 'destroy']);
    });

    // PRODUCT ROUTES
    Route::prefix('products')->group(function () {
        Route::get('/', [ProductController::class, 'index']);
        Route::get('/filter', [ProductController::class, 'filter']);   
        Route::get('/search', [ProductController::class, 'search']);
        Route::post('/store', [ProductController::class, 'store']);
        Route::patch('/{id}', [ProductController::class, 'update']);
        Route::delete('/delete/{id}', [ProductController::class, 'destroy']);
    });

    // MATERIAL ROUTES
    Route::prefix('materials')->group(function () {
        Route::get('/', [MaterialController::class, 'index']);
        Route::post('/store', [MaterialController::class, 'store']);
        Route::patch('/{id}', [MaterialController::class, 'update']);
        Route::delete('/delete/{id}', [MaterialController::class, 'destroy']);
    });

    // PRODUCT IMAGE ROUTES
    Route::prefix('product-images')->group(function () {
        Route::get('/', [ProductImageController::class, 'index']);
        Route::post('/store', [ProductImageController::class, 'store']);
        Route::patch('/{id}', [ProductImageController::class, 'update']);
        Route::delete('/delete/{id}', [ProductImageController::class, 'destroy']);
        Route::post('/reorder', [ProductImageController::class, 'reorder']);
    });

    // PRODUCT SIZE IMAGE ROUTES 
    Route::prefix('product-size-image')->group(function () {
        Route::get('/{product_id}', [ProductSizeImageController::class, 'show']);
        Route::post('/store', [ProductSizeImageController::class, 'store']);
        Route::patch('/{product_id}', [ProductSizeImageController::class, 'update']);
        Route::delete('/delete/{product_id}', [ProductSizeImageController::class, 'destroy']);
    });

    // Logout
    Route::post('logout', [AuthController::class, 'logout']);
});
