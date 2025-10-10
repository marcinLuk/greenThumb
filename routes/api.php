<?php

use App\Http\Controllers\Api\JournalEntryController;
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
});
