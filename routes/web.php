<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DesignSystemController;

Route::get('/', function () {
    return view('welcome');
});

// Design System Documentation Routes
Route::prefix('design-system')->name('design-system.')->group(function () {
    Route::get('/', [DesignSystemController::class, 'index'])->name('index');
    Route::get('/component/{component}', [DesignSystemController::class, 'component'])->name('component');
});

// Design System API Routes (for HTMX)
Route::prefix('api/ds')->name('api.ds.')->group(function () {
    Route::post('/button/{action}', [DesignSystemController::class, 'buttonAction'])->name('button');
});
