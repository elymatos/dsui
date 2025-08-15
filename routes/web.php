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
    Route::get('/showcase', [DesignSystemController::class, 'showcase'])->name('showcase');
});

// Design System API Routes (for HTMX)
Route::prefix('api/ds')->name('api.ds.')->group(function () {
    Route::post('/button/{action}', [DesignSystemController::class, 'buttonAction'])->name('button');
    Route::post('/modal/{action}', [DesignSystemController::class, 'modalAction'])->name('modal');
    Route::post('/dropdown/{action}', [DesignSystemController::class, 'dropdownAction'])->name('dropdown');
    Route::post('/data-table/{action}', [DesignSystemController::class, 'dataTableAction'])->name('data-table');
    Route::post('/form-wizard/{action}', [DesignSystemController::class, 'formWizardAction'])->name('form-wizard');
    Route::get('/component/{component}', [DesignSystemController::class, 'componentApi'])->name('component.api');
});
