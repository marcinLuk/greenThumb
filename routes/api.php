<?php

use App\Http\Controllers\Api\JournalEntryController;
use App\Http\Controllers\Api\SearchController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group.
|
*/

// Search API endpoint
Route::middleware(['auth:sanctum', 'throttle:10,1'])->group(function () {
    Route::post('/search', [SearchController::class, 'search'])
        ->name('api.search');
});

// Journal Entry API endpoints
Route::middleware(['auth:sanctum', 'throttle:60,1'])->group(function () {
    Route::get('/journal-entries', [JournalEntryController::class, 'index'])
        ->name('api.journal-entries.index');
    Route::post('/journal-entries', [JournalEntryController::class, 'store'])
        ->name('api.journal-entries.store');
    Route::get('/journal-entries/date-range', [JournalEntryController::class, 'dateRange'])
        ->name('api.journal-entries.date-range');
    Route::get('/journal-entries/{id}', [JournalEntryController::class, 'show'])
        ->name('api.journal-entries.show');
    Route::put('/journal-entries/{id}', [JournalEntryController::class, 'update'])
        ->name('api.journal-entries.update');
    Route::delete('/journal-entries/{id}', [JournalEntryController::class, 'destroy'])
        ->name('api.journal-entries.destroy');
    Route::get('journal-entries/count', [JournalEntryController::class, 'getEntryCount'])
        ->name('api.journal-entries.entry-count');
});
