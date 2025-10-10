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
});
