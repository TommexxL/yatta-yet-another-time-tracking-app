<?php

namespace App\Http\Controllers;

use App\Enums\TimeEntryCorrectionStatus;
use App\Enums\TimeEntryStatus;
use App\Models\Schedule;
use App\Models\TimeEntry;
use App\Models\TimeEntryCorrection;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ManageController extends Controller
{
    public function overview(): View
    {
        $manager = $this->manager();

        $pendingCorrections = TimeEntryCorrection::query()
            ->with(['user', 'timeEntry'])
            ->where('company_id', $manager->company_id)
            ->where('status', TimeEntryCorrectionStatus::Pending->value)
            ->oldest()
            ->get();

        $employees = User::query()
            ->with('schedules')
            ->where('company_id', $manager->company_id)
            ->where('id', '!=', $manager->id)
            ->orderBy('name')
            ->get();

        $schedules = Schedule::query()
            ->where('company_id', $manager->company_id)
            ->where('active', true)
            ->orderBy('name')
            ->get();

        return view('manage.overview', [
            'manager' => $manager,
            'pendingCorrections' => $pendingCorrections,
            'employees' => $employees,
            'schedules' => $schedules,
        ]);
    }

    public function approveCorrection(Request $request, TimeEntryCorrection $correction): RedirectResponse
    {
        $manager = $this->manager();
        $this->authorizeCorrection($correction, $manager);

        $validated = $request->validate([
            'manager_notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $entry = $correction->timeEntry ?: TimeEntry::create([
            'company_id' => $correction->company_id,
            'user_id' => $correction->user_id,
            'date' => $correction->date,
            'status' => TimeEntryStatus::Corrected,
        ]);

        $entry->update([
            'clock_in' => $correction->requested_clock_in ?? $correction->current_clock_in,
            'clock_out' => $correction->requested_clock_out ?? $correction->current_clock_out,
            'status' => TimeEntryStatus::Corrected,
        ]);

        $correction->update([
            'time_entry_id' => $entry->id,
            'status' => TimeEntryCorrectionStatus::Approved,
            'reviewed_by' => $manager->id,
            'reviewed_at' => now(),
            'manager_notes' => $validated['manager_notes'] ?? null,
        ]);

        return redirect()
            ->route('manage.overview')
            ->with('success', 'Correction request approved.');
    }

    public function denyCorrection(Request $request, TimeEntryCorrection $correction): RedirectResponse
    {
        $manager = $this->manager();
        $this->authorizeCorrection($correction, $manager);

        $validated = $request->validate([
            'manager_notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $correction->update([
            'status' => TimeEntryCorrectionStatus::Denied,
            'reviewed_by' => $manager->id,
            'reviewed_at' => now(),
            'manager_notes' => $validated['manager_notes'] ?? null,
        ]);

        return redirect()
            ->route('manage.overview')
            ->with('success', 'Correction request denied.');
    }

    public function setEmployeeSchedule(Request $request, User $employee): RedirectResponse
    {
        $manager = $this->manager();

        abort_unless($employee->company_id === $manager->company_id, 403);

        $validated = $request->validate([
            'schedule_id' => ['required', 'integer'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
        ]);

        $schedule = Schedule::query()
            ->where('company_id', $manager->company_id)
            ->where('active', true)
            ->findOrFail($validated['schedule_id']);

        $employee->schedules()->detach();
        $employee->schedules()->attach($schedule->id, [
            'starts_at' => $validated['starts_at'] ?? now()->toDateString(),
            'ends_at' => $validated['ends_at'] ?? null,
        ]);

        return redirect()
            ->route('manage.overview')
            ->with('success', "Schedule set for {$employee->name}.");
    }

    private function manager(): User
    {
        /** @var User $user */
        $user = Auth::user();

        abort_unless($user->hasRole('manager'), 403);

        return $user;
    }

    private function authorizeCorrection(TimeEntryCorrection $correction, User $manager): void
    {
        abort_unless($correction->company_id === $manager->company_id, 403);
        abort_unless($correction->status === TimeEntryCorrectionStatus::Pending, 403);
    }
}
