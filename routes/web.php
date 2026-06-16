<?php

use App\Http\Controllers\TimeEntryController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/profile', function () {
    return view('profile', [
        'user' => auth()->user()->loadMissing('company'),
        'isClockedIn' => auth()->user()->isClockedIn(),
    ]);
})->middleware('auth')->name('profile');


Route::middleware('auth')->group(function () {
    Route::get('/schedule', [TimeEntryController::class, 'schedule'])
        ->name('schedule');

    Route::post('/clock-in', [TimeEntryController::class, 'clockIn'])
        ->name('clock-in');

    Route::post('/clock-out', [TimeEntryController::class, 'clockOut'])
        ->name('clock-out');

    Route::get('/time-entry-corrections/{date}/create', [TimeEntryController::class, 'createCorrection'])
        ->name('time-entry.correction.create');

    Route::post('/time-entry-corrections/{date}', [TimeEntryController::class, 'storeCorrection'])
        ->name('time-entry.correction.store');
});
