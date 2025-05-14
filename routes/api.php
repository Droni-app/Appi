<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Middleware\SiteMiddleware;

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');

Route::middleware(SiteMiddleware::class)->group(function () {
  // Auth Routes
  Route::post('/auth/login', [AuthController::class, 'login'])->name('auth.login');
  Route::post('/auth/login-provider', [AuthController::class, 'loginProvider'])->name('auth.login.provider');
  Route::post('/auth/logout', [AuthController::class, 'logout'])->name('auth.logout')->middleware('auth:sanctum');
  Route::get('/auth/me', [AuthController::class, 'me'])->name('auth.me')->middleware('auth:sanctum');
});
