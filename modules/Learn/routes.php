<?php

use Illuminate\Support\Facades\Route;
use App\Http\Middleware\SiteMiddleware;
use Modules\Learn\Http\Controllers\CourseController;
use Modules\Learn\Http\Controllers\CourseLessonController;

Route::prefix('/api/learn')->middleware(SiteMiddleware::class)->group(function () {
  /* Public Layer */
  Route::apiResource('courses', CourseController::class)->only(['index', 'show']);
  Route::apiResource('courses.lessons', CourseLessonController::class)->only(['index', 'show']);
  /* Private Layer */
  Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('courses', CourseController::class)->only(['store', 'update', 'destroy']);
    Route::apiResource('courses.lessons', CourseLessonController::class)->only(['store', 'update', 'destroy']);
  });
});
