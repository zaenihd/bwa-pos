<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\CustomerController;
use App\Http\Controllers\Api\V1\ProductCategoryController;
use App\Http\Controllers\Api\V1\ProductCategoryImageController;
use App\Http\Controllers\Api\V1\ProductController;
use App\Http\Controllers\Api\V1\ProductImageController;
use App\Http\Controllers\Api\V1\TransactionController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/me', [AuthController::class, 'me']);
        Route::get('product-categories/options', [ProductCategoryController::class, 'option']);
        Route::post('/product-categories/{id}/image', [ProductCategoryImageController::class, 'store']);
        Route::apiResource('product-categories', ProductCategoryController::class);

        Route::post('/products/{id}/image', [ProductImageController::class, 'store']);
        Route::apiResource('products', ProductController::class);

        Route::get('customers/options', [CustomerController::class, 'option']);
        Route::apiResource('customers', CustomerController::class);

        Route::apiResource('transactions', TransactionController::class)->only(['index', 'store', 'show']);
    });
});
