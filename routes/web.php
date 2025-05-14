<?php

use Illuminate\Support\Facades\Route;

Route::get('/', fn() => redirect('/app'));

Route::get('/app', fn() => view('app'));
// Nueva ruta que captura todas las URLs que comienzan con /app/ seguido de cualquier cosa
Route::get('/app/{any}', fn() => view('app'))->where('any', '.*');
