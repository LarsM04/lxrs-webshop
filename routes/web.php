<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\LoginController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PresetController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');

Route::get('/presets', [PresetController::class, 'index'])->name('presets.index');
Route::get('/presets/{slug}', [PresetController::class, 'show'])->name('presets.show');

/*
 * Beheeromgeving. Alleen de loginpagina is open; de rest vraagt om een login.
 * Waar gasten en ingelogde beheerders heen gestuurd worden, staat in
 * bootstrap/app.php.
 */
Route::prefix('admin')->name('admin.')->group(function () {
    Route::middleware('guest')->group(function () {
        Route::get('login', [LoginController::class, 'create'])->name('login');
        Route::post('login', [LoginController::class, 'store'])->name('login.store');
    });

    Route::middleware('auth')->group(function () {
        Route::get('/', DashboardController::class)->name('dashboard');
        Route::post('logout', [LoginController::class, 'destroy'])->name('logout');
    });
});
