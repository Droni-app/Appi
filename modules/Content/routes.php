<?php

use Illuminate\Support\Facades\Route;
use App\Http\Middleware\SiteMiddleware;
use Modules\Content\Http\Controllers\AttachmentController;
use Modules\Content\Http\Controllers\CategoryController;
use Modules\Content\Http\Controllers\PostController;


Route::prefix('/api/content')->middleware(SiteMiddleware::class)->group(function () {
  /* Public Layer */
  Route::apiResource('categories', CategoryController::class)->only(['index', 'show']);
  Route::apiResource('posts', PostController::class)->only(['index', 'show']);
  /* Private Layer */
  Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('attachments', AttachmentController::class)->only(['index', 'store', 'destroy']);
    Route::apiResource('categories', CategoryController::class)->only(['store', 'update', 'destroy']);
    Route::apiResource('posts', PostController::class)->only(['store', 'update', 'destroy']);
  });
});
