<?php

use Illuminate\Support\Facades\Route;
use App\Http\Middleware\SiteMiddleware;
use Modules\Content\Http\Controllers\AttachmentController;


Route::prefix('/api/content')->middleware(SiteMiddleware::class)->group(function () {
  /* Public Layer */
  /* Private Layer */
  Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('attachments', AttachmentController::class)->only(['index', 'store', 'destroy']);
  });
});
