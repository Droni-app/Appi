<?php

use Illuminate\Support\Facades\Route;
use App\Http\Middleware\SiteMiddleware;
use Modules\Codevs\Http\Controllers\ChallengeController;


Route::prefix('/api/codevs')->middleware(SiteMiddleware::class)->group(function () {
  /* Public Layer */
  Route::apiResource('challenges', ChallengeController::class)->only(['index', 'show']);
  /* Private Layer */
  Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('challenges', ChallengeController::class)->only(['store', 'update', 'destroy']);
  });
});
