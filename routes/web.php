<?php

use App\Http\Controllers\ManageController;
use App\Http\Controllers\TimeEntryController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/profile');


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

    Route::get('/manage', [ManageController::class, 'overview'])
        ->name('manage.overview');

    Route::get('/manage/schedules', [ManageController::class, 'schedule'])
        ->name('manage.schedule');

    Route::post('/manage/time-entry-corrections/{correction}/approve', [ManageController::class, 'approveCorrection'])
        ->name('manage.time-entry-corrections.approve');

    Route::post('/manage/time-entry-corrections/{correction}/deny', [ManageController::class, 'denyCorrection'])
        ->name('manage.time-entry-corrections.deny');

    Route::post('/manage/employees/{employee}/schedule', [ManageController::class, 'setEmployeeSchedule'])
        ->name('manage.employees.schedule');

    Route::post('/manage/schedules', [ManageController::class, 'storeSchedule'])
        ->name('manage.schedules.store');

    Route::put('/manage/schedules/{schedule}', [ManageController::class, 'updateSchedule'])
        ->name('manage.schedules.update');

    Route::delete('/manage/schedules/{schedule}', [ManageController::class, 'destroySchedule'])
        ->name('manage.schedules.destroy');
});
