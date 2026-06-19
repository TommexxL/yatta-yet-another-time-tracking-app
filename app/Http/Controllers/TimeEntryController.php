<?php

namespace App\Http\Controllers;

use App\Enums\TimeEntryCorrectionStatus;
use App\Enums\TimeEntryStatus;
use App\Models\TimeEntry;
use App\Models\TimeEntryCorrection;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class TimeEntryController extends Controller
{
    public function clockIn(): RedirectResponse
    {
        $user = Auth::user();

        $existingEntry = TimeEntry::query()
            ->where('user_id', $user->id)
            ->whereDate('date', today())
            ->whereNull('clock_out')
            ->first();

        if ($existingEntry) {
            return back()->with('error', 'You are already clocked in.');
        }

        TimeEntry::create([
            'company_id' => $user->company_id,
            'user_id' => $user->id,
            'date' => today(),
            'clock_in' => now()->format('H:i:s'),
            'status' => TimeEntryStatus::Approved,
        ]);

        return back()->with('success', 'Clocked in successfully.');
    }

    public function clockOut(): RedirectResponse
    {
        $user = Auth::user();

        $entry = TimeEntry::query()
            ->where('user_id', $user->id)
            ->whereDate('date', today())
            ->whereNull('clock_out')
            ->latest()
            ->first();

        if (! $entry) {
            return back()->with('error', 'No active clock-in found.');
        }

        $entry->update([
            'clock_out' => now()->format('H:i:s'),
        ]);

        return back()->with('success', 'Clocked out successfully.');
    }

    public function createCorrection(string $date): View
    {
        $user = Auth::user();
        $correctionDate = $this->currentWeekDateOrFail($date);
        $entry = $this->timeEntryForDate($correctionDate);
        $pendingCorrection = $user->timeEntryCorrections()
            ->whereDate('date', $correctionDate)
            ->where('status', TimeEntryCorrectionStatus::Pending->value)
            ->latest()
            ->first();

        return view('time-entry-correction', [
            'user' => $user,
            'date' => $correctionDate,
            'entry' => $entry,
            'pendingCorrection' => $pendingCorrection,
        ]);
    }

    public function storeCorrection(Request $request, string $date): RedirectResponse
    {
        $user = Auth::user();
        $correctionDate = $this->currentWeekDateOrFail($date);
        $entry = $this->timeEntryForDate($correctionDate);

        $validated = $request->validate([
            'requested_clock_in' => ['nullable', 'date_format:H:i', 'required_without:requested_clock_out'],
            'requested_clock_out' => ['nullable', 'date_format:H:i', 'required_without:requested_clock_in'],
            'reason' => ['required', 'string', 'max:2000'],
        ]);

        $hasPendingCorrection = $user->timeEntryCorrections()
            ->whereDate('date', $correctionDate)
            ->where('status', TimeEntryCorrectionStatus::Pending->value)
            ->exists();

        if ($hasPendingCorrection) {
            return redirect()
                ->route('schedule')
                ->with('error', 'There is already a pending correction request for that day.');
        }

        TimeEntryCorrection::create([
            'company_id' => $user->company_id,
            'user_id' => $user->id,
            'time_entry_id' => $entry?->id,
            'date' => $correctionDate->toDateString(),
            'current_clock_in' => $entry?->clock_in,
            'current_clock_out' => $entry?->clock_out,
            'requested_clock_in' => $validated['requested_clock_in'] ?? null,
            'requested_clock_out' => $validated['requested_clock_out'] ?? null,
            'reason' => $validated['reason'],
            'status' => TimeEntryCorrectionStatus::Pending,
        ]);

        return redirect()
            ->route('schedule')
            ->with('success', 'Correction request submitted for manager review.');
    }

    private function currentWeekDateOrFail(string $date): CarbonImmutable
    {
        try {
            $correctionDate = CarbonImmutable::parse($date)->startOfDay();
        } catch (\Throwable) {
            abort(404);
        }

        $weekStart = CarbonImmutable::now()->startOfWeek(CarbonInterface::MONDAY)->startOfDay();
        $weekEnd = $weekStart->endOfWeek(CarbonInterface::SUNDAY)->startOfDay();

        abort_unless($correctionDate->betweenIncluded($weekStart, $weekEnd), 404);

        return $correctionDate;
    }

    private function timeEntryForDate(CarbonImmutable $date): ?TimeEntry
    {
        return Auth::user()->timeEntries()
            ->whereDate('date', $date)
            ->latest()
            ->first();
    }
}
