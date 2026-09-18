<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\PresetController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');

Route::get('/presets', [PresetController::class, 'index'])->name('presets.index');
Route::get('/presets/{slug}', [PresetController::class, 'show'])->name('presets.show');
