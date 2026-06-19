<?php

namespace App\Http\Controllers;

use App\Enums\LeaveRequestStatus;
use App\Enums\SickLeaveStatus;
use App\Enums\TimeEntryCorrectionStatus;
use App\Models\LeaveRequest;
use App\Models\Schedule;
use App\Models\ScheduleDay;
use App\Models\SickLeave;
use App\Models\TimeEntry;
use App\Models\TimeEntryCorrection;
use App\Models\User;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;

class ScheduleController extends Controller
{
    public function index(): View
    {
        $user = Auth::user();
        $weekStart = CarbonImmutable::now()->startOfWeek(CarbonInterface::MONDAY);
        $weekEnd = $weekStart->endOfWeek(CarbonInterface::SUNDAY);
        $schedule = $user->activeSchedule();

        $scheduleDays = $schedule
            ? $schedule->days()->orderBy('weekday')->get()->keyBy('weekday')
            : collect();

        $entries = $user->timeEntries()
            ->whereBetween('date', [$weekStart->toDateString(), $weekEnd->toDateString()])
            ->orderBy('date')
            ->get()
            ->keyBy(fn (TimeEntry $entry): string => $entry->date->toDateString());

        $corrections = $user->timeEntryCorrections()
            ->whereBetween('date', [$weekStart->toDateString(), $weekEnd->toDateString()])
            ->where('status', TimeEntryCorrectionStatus::Pending->value)
            ->latest()
            ->get()
            ->keyBy(fn (TimeEntryCorrection $correction): string => $correction->date->toDateString());

        $leaveRequests = $user->leaveRequests()
            ->where('status', LeaveRequestStatus::Pending->value)
            ->where('start_date', '<=', $weekEnd->toDateString())
            ->where('end_date', '>=', $weekStart->toDateString())
            ->get();

        $approvedLeaveRequests = $user->leaveRequests()
            ->where('status', LeaveRequestStatus::Approved->value)
            ->where('start_date', '<=', $weekEnd->toDateString())
            ->where('end_date', '>=', $weekStart->toDateString())
            ->get();

        $sickLeaves = $user->sickLeaves()
            ->where('status', SickLeaveStatus::Reported->value)
            ->where('start_date', '<=', $weekEnd->toDateString())
            ->where(function ($query) use ($weekStart): void {
                $query->whereNull('expected_return_date')
                    ->orWhere('expected_return_date', '>=', $weekStart->toDateString());
            })
            ->get();

        $approvedSickLeaves = $user->sickLeaves()
            ->where('status', SickLeaveStatus::Approved->value)
            ->where('start_date', '<=', $weekEnd->toDateString())
            ->where(function ($query) use ($weekStart): void {
                $query->whereNull('expected_return_date')
                    ->orWhere('expected_return_date', '>=', $weekStart->toDateString());
            })
            ->get();

        $weekDays = collect(range(0, 6))->map(function (int $offset) use ($weekStart, $scheduleDays, $entries, $corrections, $leaveRequests, $sickLeaves, $approvedLeaveRequests, $approvedSickLeaves): array {
            $date = $weekStart->addDays($offset);

            return [
                'date' => $date,
                'scheduleDay' => $scheduleDays->get($date->dayOfWeekIso),
                'entry' => $entries->get($date->toDateString()),
                'correction' => $corrections->get($date->toDateString()),
                'leaveRequest' => $leaveRequests->first(fn (LeaveRequest $leaveRequest): bool => $date->betweenIncluded($leaveRequest->start_date, $leaveRequest->end_date)),
                'sickLeave' => $sickLeaves->first(fn (SickLeave $sickLeave): bool => $date->betweenIncluded($sickLeave->start_date, $sickLeave->expected_return_date ?? $sickLeave->start_date)),
                'approvedLeaveRequest' => $approvedLeaveRequests->first(fn (LeaveRequest $leaveRequest): bool => $date->betweenIncluded($leaveRequest->start_date, $leaveRequest->end_date)),
                'approvedSickLeave' => $approvedSickLeaves->first(fn (SickLeave $sickLeave): bool => $date->betweenIncluded($sickLeave->start_date, $sickLeave->expected_return_date ?? $sickLeave->start_date)),
            ];
        });

        return view('schedule', [
            'user' => $user->loadMissing('company'),
            'schedule' => $schedule,
            'weekStart' => $weekStart,
            'weekEnd' => $weekEnd,
            'weekDays' => $weekDays,
        ]);
    }

    public function manage(): View
    {
        $manager = $this->manager();

        return view('manage.schedule', [
            'manager' => $manager,
            'managedSchedules' => $this->managedSchedules($manager),
            'weekdays' => $this->weekdays(),
        ]);
    }

    public function store(Request $request): RedirectResponse
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

    public function update(Request $request, Schedule $schedule): RedirectResponse
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

    public function destroy(Schedule $schedule): RedirectResponse
    {
        $manager = $this->manager();
        abort_unless($schedule->company_id === $manager->company_id, 403);

        $schedule->users()->detach();
        $schedule->delete();

        return redirect()
            ->route('manage.schedule')
            ->with('success', 'Schedule removed.');
    }

    public function assignEmployee(Request $request, User $employee): RedirectResponse
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
