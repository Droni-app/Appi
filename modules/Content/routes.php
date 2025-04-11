<?php

use Illuminate\Support\Facades\Route;
use App\Http\Middleware\SiteMiddleware;
use Modules\Content\Http\Controllers\AttachmentController;
use Modules\Content\Http\Controllers\CategoryController;


Route::prefix('/api/content')->middleware(SiteMiddleware::class)->group(function () {
  /* Public Layer */
  Route::apiResource('categories', CategoryController::class)->only(['index', 'show']);
  /* Private Layer */
  Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('attachments', AttachmentController::class)->only(['index', 'store', 'destroy']);
    Route::apiResource('categories', CategoryController::class)->only(['store', 'update', 'destroy']);
  });
});
