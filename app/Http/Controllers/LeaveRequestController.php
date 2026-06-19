<?php

namespace App\Http\Controllers;

use App\Enums\LeaveRequestStatus;
use App\Enums\SickLeaveStatus;
use App\Models\LeaveRequest;
use App\Models\SickLeave;
use Carbon\CarbonImmutable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class LeaveRequestController extends Controller
{
    public function create(Request $request): View
    {
        $date = $this->defaultDate($request->query('date'));
        $type = $request->query('type') === 'sick' ? 'sick' : 'vacation';

        return view('leave-request', [
            'user' => Auth::user(),
            'defaultDate' => $date,
            'defaultType' => $type,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $user = Auth::user();

        $validated = $request->validate([
            'type' => ['required', 'in:vacation,sick'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'reason' => ['nullable', 'string', 'max:2000'],
        ]);

        $startDate = CarbonImmutable::parse($validated['start_date'])->startOfDay();
        $endDate = CarbonImmutable::parse($validated['end_date'])->startOfDay();

        if ($validated['type'] === 'vacation') {
            if ($this->hasPendingVacationOverlap($user->id, $startDate, $endDate)) {
                return back()
                    ->withInput()
                    ->with('error', 'You already have a pending vacation request in that period.');
            }

            LeaveRequest::create([
                'company_id' => $user->company_id,
                'user_id' => $user->id,
                'leave_type' => 'vacation',
                'start_date' => $startDate->toDateString(),
                'end_date' => $endDate->toDateString(),
                'days' => $startDate->diffInDays($endDate) + 1,
                'reason' => $validated['reason'] ?? null,
                'status' => LeaveRequestStatus::Pending,
            ]);
        } else {
            if ($this->hasPendingSickOverlap($user->id, $startDate, $endDate)) {
                return back()
                    ->withInput()
                    ->with('error', 'You already have a pending sick leave request in that period.');
            }

            SickLeave::create([
                'company_id' => $user->company_id,
                'user_id' => $user->id,
                'start_date' => $startDate->toDateString(),
                'expected_return_date' => $endDate->toDateString(),
                'notes' => $validated['reason'] ?? null,
                'status' => SickLeaveStatus::Reported,
            ]);
        }

        return redirect()
            ->route('schedule')
            ->with('success', 'Leave request submitted for manager review.');
    }

    private function defaultDate(mixed $date): string
    {
        try {
            return CarbonImmutable::parse((string) $date)->toDateString();
        } catch (\Throwable) {
            return today()->toDateString();
        }
    }

    private function hasPendingVacationOverlap(int $userId, CarbonImmutable $startDate, CarbonImmutable $endDate): bool
    {
        return LeaveRequest::query()
            ->where('user_id', $userId)
            ->where('status', LeaveRequestStatus::Pending->value)
            ->where('start_date', '<=', $endDate->toDateString())
            ->where('end_date', '>=', $startDate->toDateString())
            ->exists();
    }

    private function hasPendingSickOverlap(int $userId, CarbonImmutable $startDate, CarbonImmutable $endDate): bool
    {
        return SickLeave::query()
            ->where('user_id', $userId)
            ->where('status', SickLeaveStatus::Reported->value)
            ->where('start_date', '<=', $endDate->toDateString())
            ->where(function ($query) use ($startDate): void {
                $query->whereNull('expected_return_date')
                    ->orWhere('expected_return_date', '>=', $startDate->toDateString());
            })
            ->exists();
    }
}
