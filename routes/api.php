<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\ShellController;
use App\Http\Controllers\Api\TariffController;
use App\Http\Controllers\SettingsController;
use Illuminate\Support\Facades\Route;

Route::post('/shell/resolve', [ShellController::class, 'resolve'])
    ->middleware('throttle:30,1')
    ->name('api.shell.resolve');

Route::post('/auth/login', [AuthController::class, 'login'])
    ->middleware('throttle:30,1')
    ->name('api.auth.login');

Route::get('/dashboards/{dashboard:slug}/products', ProductController::class)
    ->middleware('throttle:60,1')
    ->name('api.products.index');

Route::get('/dashboards/{dashboard:slug}/tariffs', TariffController::class)
    ->middleware('throttle:60,1')
    ->name('api.tariffs.index');

Route::get('/dashboards/{dashboard:slug}/settings/{section}/{key}', [SettingsController::class, 'showSetting'])
    ->where([
        'section' => '[a-z_]+',
        'key' => '[a-z_]+',
    ])
    ->middleware('throttle:60,1')
    ->name('api.settings.show');
