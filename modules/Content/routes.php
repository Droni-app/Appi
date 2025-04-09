<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Middleware\SiteMiddleware;
use Illuminate\Http\Request;


Route::prefix('/api/content')->middleware(SiteMiddleware::class)->group(function () {
  Route::get('/hola', fn(Request $request) => dd($request->site));
});
