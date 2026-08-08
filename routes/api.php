<?php

use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\SettingsController;
use Illuminate\Support\Facades\Route;

Route::get('/products', ProductController::class)
    ->middleware('throttle:60,1')
    ->name('api.products.index');

Route::get('/settings/{section}/{key}', [SettingsController::class, 'showSetting'])
    ->where([
        'section' => '[a-z_]+',
        'key' => '[a-z_]+',
    ])
    ->middleware('throttle:60,1')
    ->name('api.settings.show');
