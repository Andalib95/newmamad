<?php

use App\Http\Controllers\Api\ArticleController as ApiArticleController;
use App\Http\Controllers\Api\CategoryController as ApiCategoryController;
use App\Http\Controllers\Api\ContactController as ApiContactController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// API Routes عمومی
Route::prefix('v1')->group(function () {
    // مقالات
    Route::get('/articles', [ApiArticleController::class, 'index']);
    Route::get('/articles/{article}', [ApiArticleController::class, 'show']);
    Route::get('/articles/category/{category}', [ApiArticleController::class, 'byCategory']);
    Route::get('/articles/tag/{tag}', [ApiArticleController::class, 'byTag']);

    // دسته‌بندی‌ها
    Route::get('/categories', [ApiCategoryController::class, 'index']);
    Route::get('/categories/{category}', [ApiCategoryController::class, 'show']);

    // تماس
    Route::post('/contact', [ApiContactController::class, 'store']);

    // سوالات متداول
    Route::get('/faqs', [ApiFaqController::class, 'index']);

    // خدمات
    Route::get('/services', [ApiServiceController::class, 'index']);

    // تیم
    Route::get('/team', [ApiTeamController::class, 'index']);
});

// API Routes مدیریت (نیاز به احراز هویت)
Route::prefix('v1/admin')->middleware(['auth:sanctum'])->group(function () {
    Route::apiResource('articles', ApiArticleController::class)->except(['index', 'show']);
    // سایر routes مدیریتی...
});
