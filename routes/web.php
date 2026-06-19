<?php

use App\Http\Controllers\ManageController;
use App\Http\Controllers\LeaveRequestController;
use App\Http\Controllers\ScheduleController;
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
    Route::get('/schedule', [ScheduleController::class, 'index'])
        ->name('schedule');

    Route::post('/clock-in', [TimeEntryController::class, 'clockIn'])
        ->name('clock-in');

    Route::post('/clock-out', [TimeEntryController::class, 'clockOut'])
        ->name('clock-out');

    Route::get('/time-entry-corrections/{date}/create', [TimeEntryController::class, 'createCorrection'])
        ->name('time-entry.correction.create');

    Route::post('/time-entry-corrections/{date}', [TimeEntryController::class, 'storeCorrection'])
        ->name('time-entry.correction.store');

    Route::get('/leave-requests/create', [LeaveRequestController::class, 'create'])
        ->name('leave-request.create');

    Route::post('/leave-requests', [LeaveRequestController::class, 'store'])
        ->name('leave-request.store');

    Route::get('/manage', [ManageController::class, 'overview'])
        ->name('manage.overview');

    Route::get('/manage/schedules', [ScheduleController::class, 'manage'])
        ->name('manage.schedule');

    Route::post('/manage/time-entry-corrections/{correction}/approve', [ManageController::class, 'approveCorrection'])
        ->name('manage.time-entry-corrections.approve');

    Route::post('/manage/time-entry-corrections/{correction}/deny', [ManageController::class, 'denyCorrection'])
        ->name('manage.time-entry-corrections.deny');

    Route::post('/manage/leave-requests/{leaveRequest}/approve', [ManageController::class, 'approveLeaveRequest'])
        ->name('manage.leave-requests.approve');

    Route::post('/manage/leave-requests/{leaveRequest}/deny', [ManageController::class, 'denyLeaveRequest'])
        ->name('manage.leave-requests.deny');

    Route::post('/manage/sick-leaves/{sickLeave}/approve', [ManageController::class, 'approveSickLeave'])
        ->name('manage.sick-leaves.approve');

    Route::post('/manage/sick-leaves/{sickLeave}/deny', [ManageController::class, 'denySickLeave'])
        ->name('manage.sick-leaves.deny');

    Route::post('/manage/employees/{employee}/schedule', [ScheduleController::class, 'assignEmployee'])
        ->name('manage.employees.schedule');

    Route::post('/manage/schedules', [ScheduleController::class, 'store'])
        ->name('manage.schedules.store');

    Route::put('/manage/schedules/{schedule}', [ScheduleController::class, 'update'])
        ->name('manage.schedules.update');

    Route::delete('/manage/schedules/{schedule}', [ScheduleController::class, 'destroy'])
        ->name('manage.schedules.destroy');
});
