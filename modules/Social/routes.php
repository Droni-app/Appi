<?php

use Illuminate\Support\Facades\Route;
use App\Http\Middleware\SiteMiddleware;
use Modules\Social\Http\Controllers\CommentController;


Route::prefix('/api/social')->middleware(SiteMiddleware::class)->group(function () {
  /* Public Layer */
  Route::apiResource('comments', CommentController::class)->only(['index']);
  /* Private Layer */
  Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('comments', CommentController::class)->only(['store', 'update', 'destroy']);
  });
});
