<?php

use App\Http\Controllers\AdminAuthController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')->group(function () {
    Route::post('/login', [AdminAuthController::class, 'login']);
    Route::middleware('auth:admin')->get('/me', [AdminAuthController::class, 'me']);
});

Route::get('/test-jwt-ttl', function () {
    $guard = Auth::guard('admin');

    return response()->json([
        'class' => get_class($guard),
        'ttl' => $guard->factory()->getTTL(),
    ]);
});
