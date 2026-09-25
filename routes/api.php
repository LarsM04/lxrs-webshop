<?php

use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\PresetController;
use App\Http\Middleware\EnsureApiKey;
use Illuminate\Support\Facades\Route;

/*
 * Alles hier staat onder /api, dus 'presets' wordt /api/presets.
 */

// Lezen: open voor iedereen, het staat toch al op de website.
Route::get('categories', [CategoryController::class, 'index'])->name('api.categories.index');
Route::apiResource('presets', PresetController::class)->only(['index', 'show'])->names('api.presets');

// Schrijven: alleen met de API-sleutel in de header X-API-Key.
Route::middleware(EnsureApiKey::class)->group(function () {
    Route::apiResource('presets', PresetController::class)->only(['store', 'update', 'destroy'])->names('api.presets');
});
