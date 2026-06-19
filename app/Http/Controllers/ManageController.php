<?php

namespace App\Http\Controllers;

use App\Enums\TimeEntryCorrectionStatus;
use App\Enums\TimeEntryStatus;
use App\Models\Schedule;
use App\Models\ScheduleDay;
use App\Models\TimeEntry;
use App\Models\TimeEntryCorrection;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
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

    public function schedule(): View
    {
        $manager = $this->manager();

        return view('manage.schedule', [
            'manager' => $manager,
            'managedSchedules' => $this->managedSchedules($manager),
            'weekdays' => $this->weekdays(),
        ]);
    }

    public function storeSchedule(Request $request): RedirectResponse
    {
        $manager = $this->manager();
        $validated = $this->validateSchedule($request);

        DB::transaction(function () use ($manager, $validated): void {
            $schedule = Schedule::create([
                'company_id' => $manager->company_id,
                'name' => $validated['name'],
                'weekly_hours' => $validated['weekly_hours'],
                'active' => $validated['active'] ?? false,
            ]);

            $this->syncScheduleDays($schedule, $validated['days'] ?? []);
        });

        return redirect()
            ->route('manage.schedule')
            ->with('success', 'Schedule created.');
    }

    public function updateSchedule(Request $request, Schedule $schedule): RedirectResponse
    {
        $manager = $this->manager();
        abort_unless($schedule->company_id === $manager->company_id, 403);

        $validated = $this->validateSchedule($request);

        DB::transaction(function () use ($schedule, $validated): void {
            $schedule->update([
                'name' => $validated['name'],
                'weekly_hours' => $validated['weekly_hours'],
                'active' => $validated['active'] ?? false,
            ]);

            $this->syncScheduleDays($schedule, $validated['days'] ?? []);
        });

        return redirect()
            ->route('manage.schedule')
            ->with('success', 'Schedule updated.');
    }

    public function destroySchedule(Schedule $schedule): RedirectResponse
    {
        $manager = $this->manager();
        abort_unless($schedule->company_id === $manager->company_id, 403);

        $schedule->users()->detach();
        $schedule->delete();

        return redirect()
            ->route('manage.schedule')
            ->with('success', 'Schedule removed.');
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

    private function managedSchedules(User $manager)
    {
        return Schedule::query()
            ->with(['days' => fn ($query) => $query->orderBy('weekday')])
            ->where('company_id', $manager->company_id)
            ->orderBy('name')
            ->get();
    }

    /**
     * @return array<string, mixed>
     */
    private function validateSchedule(Request $request): array
    {
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255'],
            'weekly_hours' => ['required', 'numeric', 'min:0', 'max:168'],
            'active' => ['nullable', 'boolean'],
            'days' => ['required', 'array'],
            'days.*' => ['array'],
            'days.*.enabled' => ['nullable', 'boolean'],
            'days.*.start_time' => ['nullable', 'date_format:H:i'],
            'days.*.end_time' => ['nullable', 'date_format:H:i'],
            'days.*.break_minutes' => ['nullable', 'integer', 'min:0', 'max:1440'],
        ]);

        $validator->after(function ($validator) use ($request): void {
            $workingDays = collect($request->input('days', []))
                ->filter(fn (array $day): bool => (bool) ($day['enabled'] ?? false));

            if ($workingDays->isEmpty()) {
                $validator->errors()->add('days', 'Add at least one working day to the schedule.');
            }

            $workingDays->each(function (array $day, int|string $weekday) use ($validator): void {
                if (! array_key_exists((int) $weekday, $this->weekdays())) {
                    $validator->errors()->add('days', 'The selected weekday is invalid.');
                }

                if (blank($day['start_time'] ?? null) || blank($day['end_time'] ?? null)) {
                    $validator->errors()->add("days.{$weekday}.start_time", 'Working days need a start and end time.');

                    return;
                }

                if (($day['end_time'] ?? '') <= ($day['start_time'] ?? '')) {
                    $validator->errors()->add("days.{$weekday}.end_time", 'End time must be after start time.');
                }
            });
        });

        return $validator->validate();
    }

    /**
     * @param  array<int|string, array<string, mixed>>  $days
     */
    private function syncScheduleDays(Schedule $schedule, array $days): void
    {
        $schedule->days()->delete();

        foreach ($days as $weekday => $day) {
            if (! ($day['enabled'] ?? false)) {
                continue;
            }

            ScheduleDay::create([
                'schedule_id' => $schedule->id,
                'weekday' => (int) $weekday,
                'start_time' => $day['start_time'],
                'end_time' => $day['end_time'],
                'break_minutes' => $day['break_minutes'] ?? 0,
            ]);
        }
    }

    /**
     * @return array<int, string>
     */
    private function weekdays(): array
    {
        return [
            1 => 'Monday',
            2 => 'Tuesday',
            3 => 'Wednesday',
            4 => 'Thursday',
            5 => 'Friday',
            6 => 'Saturday',
            7 => 'Sunday',
        ];
    }
}
