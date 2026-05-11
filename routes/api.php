<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\ProductCategoryController;
use App\Http\Controllers\Api\V1\ProductCategoryImageController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/me', [AuthController::class, 'me']);
        Route::get('product-categories/options', [ProductCategoryController::class, 'option']);
        Route::post('/product-categories/{id}/image', [ProductCategoryImageController::class, 'store']);
        Route::apiResource('product-categories', ProductCategoryController::class);


    });
});
