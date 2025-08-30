<?php

use Illuminate\Support\Facades\Route;
use Laravel\Horizon\Horizon;

Route::get('/', function () {
    return view('welcome');
});

Horizon::routeMailNotificationsTo('your-email@example.com'); // 任意
// or just
Route::get('/horizon', function () {
    return redirect('/horizon/');
});
