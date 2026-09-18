<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CashRegisterController;
use App\Http\Controllers\ClubZoneController;
use App\Http\Controllers\ClubComputerController;
use App\Http\Controllers\ClubMemberController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\StockMovementController;
use App\Http\Controllers\WarehouseController;
use Illuminate\Support\Facades\Route;

// Public routes
Route::get('/', function () {
    return view('welcome');
});

// Auth routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('auth.show-login');
    Route::post('/login', [AuthController::class, 'login'])->name('auth.login');
    Route::get('/register', [AuthController::class, 'showRegister'])->name('auth.show-register');
    Route::post('/register', [AuthController::class, 'register'])->name('auth.register');
});

// Protected routes
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    Route::get('/warehouse', WarehouseController::class)->name('warehouse');
    Route::get('/club-members', [ClubMemberController::class, 'index'])->name('club-members.index');
    Route::post('/club-members', [ClubMemberController::class, 'store'])->name('club-members.store');
    Route::resource('/club-map', ClubZoneController::class)->only(['index', 'store', 'update', 'destroy']);
    Route::post('/club-map/{clubMap}/computers', [ClubZoneController::class, 'storeComputer'])->name('club-map.computers.store');
    Route::patch('/club-map/{clubMap}/layout', [ClubZoneController::class, 'updateLayout'])->name('club-map.layout');
    Route::post('/club-map/align', [ClubZoneController::class, 'align'])->name('club-map.align');
    Route::put('/club-computers/{clubComputer}', [ClubComputerController::class, 'update'])->name('club-computers.update');
    Route::patch('/club-computers/{clubComputer}/layout', ClubComputerController::class)->name('club-computers.layout');
    Route::post('/club-computers/control', [ClubComputerController::class, 'control'])->name('club-computers.control');
    Route::post('/warehouse/stock-movements', StockMovementController::class)
        ->name('warehouse.stock-movements.store');

    Route::get('/cash-register', [CashRegisterController::class, 'index'])->name('cash-register.index');
    Route::post('/cash-register/shifts', [CashRegisterController::class, 'open'])->name('cash-register.shifts.open');
    Route::post('/cash-register/transactions', [CashRegisterController::class, 'transaction'])->name('cash-register.transactions.store');
    Route::post('/cash-register/sales', [CashRegisterController::class, 'sale'])->name('cash-register.sales.store');
    Route::get('/cash-register/receipts/{cashTransaction}', [CashRegisterController::class, 'receipt'])->name('cash-register.receipt');
    Route::get('/cash-register/reports/x', [CashRegisterController::class, 'xReport'])->name('cash-register.x-report');
    Route::post('/cash-register/shifts/{cashShift}/close', [CashRegisterController::class, 'close'])->name('cash-register.shifts.close');
    Route::get('/cash-register/shifts/{cashShift}/z-report', [CashRegisterController::class, 'zReport'])->name('cash-register.z-report');
    Route::post('/cash-register/shifts/{cashShift}/z-report/printed', [CashRegisterController::class, 'markZReportPrinted'])->name('cash-register.z-report.printed');

    Route::get('/settings', function () {
        return redirect()->route('settings.club');
    })->name('settings');

    Route::get('/settings/club', [SettingsController::class, 'club'])->name('settings.club');

    Route::get('/settings/finances', [SettingsController::class, 'finances'])->name('settings.finances');

    Route::get('/settings/tariffs', [SettingsController::class, 'tariffs'])->name('settings.tariffs');

    Route::get('/settings/guests', [SettingsController::class, 'guests'])->name('settings.guests');

    Route::get('/settings/api', [SettingsController::class, 'api'])->name('settings.api');

    Route::post('/settings/save', [SettingsController::class, 'save'])->name('settings.save');

    Route::post('/logout', [AuthController::class, 'logout'])->name('auth.logout');
});
