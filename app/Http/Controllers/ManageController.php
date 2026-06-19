<?php

namespace App\Http\Controllers;

use App\Enums\TimeEntryCorrectionStatus;
use App\Enums\TimeEntryStatus;
use App\Enums\LeaveRequestStatus;
use App\Enums\SickLeaveStatus;
use App\Models\LeaveRequest;
use App\Models\Schedule;
use App\Models\SickLeave;
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

        $pendingLeaveRequests = LeaveRequest::query()
            ->with('user')
            ->where('company_id', $manager->company_id)
            ->where('status', LeaveRequestStatus::Pending->value)
            ->oldest()
            ->get();

        $pendingSickLeaves = SickLeave::query()
            ->with('user')
            ->where('company_id', $manager->company_id)
            ->where('status', SickLeaveStatus::Reported->value)
            ->oldest()
            ->get();

        $schedules = Schedule::query()
            ->where('company_id', $manager->company_id)
            ->where('active', true)
            ->orderBy('name')
            ->get();

        return view('manage.overview', [
            'manager' => $manager,
            'pendingCorrections' => $pendingCorrections,
            'pendingLeaveRequests' => $pendingLeaveRequests,
            'pendingSickLeaves' => $pendingSickLeaves,
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

    public function approveLeaveRequest(LeaveRequest $leaveRequest): RedirectResponse
    {
        $manager = $this->manager();
        $this->authorizeLeaveRequest($leaveRequest, $manager);

        $leaveRequest->update([
            'status' => LeaveRequestStatus::Approved,
            'approved_by' => $manager->id,
            'approved_at' => now(),
            'rejection_reason' => null,
        ]);

        return redirect()
            ->route('manage.overview')
            ->with('success', 'Leave request approved.');
    }

    public function denyLeaveRequest(Request $request, LeaveRequest $leaveRequest): RedirectResponse
    {
        $manager = $this->manager();
        $this->authorizeLeaveRequest($leaveRequest, $manager);

        $validated = $request->validate([
            'rejection_reason' => ['nullable', 'string', 'max:2000'],
        ]);

        $leaveRequest->update([
            'status' => LeaveRequestStatus::Denied,
            'approved_by' => $manager->id,
            'approved_at' => now(),
            'rejection_reason' => $validated['rejection_reason'] ?? null,
        ]);

        return redirect()
            ->route('manage.overview')
            ->with('success', 'Leave request denied.');
    }

    public function approveSickLeave(SickLeave $sickLeave): RedirectResponse
    {
        $manager = $this->manager();
        $this->authorizeSickLeave($sickLeave, $manager);

        $sickLeave->update([
            'status' => SickLeaveStatus::Approved,
            'approved_by' => $manager->id,
            'approved_at' => now(),
        ]);

        return redirect()
            ->route('manage.overview')
            ->with('success', 'Sick leave approved.');
    }

    public function denySickLeave(SickLeave $sickLeave): RedirectResponse
    {
        $manager = $this->manager();
        $this->authorizeSickLeave($sickLeave, $manager);

        $sickLeave->update([
            'status' => SickLeaveStatus::Denied,
            'approved_by' => $manager->id,
            'approved_at' => now(),
        ]);

        return redirect()
            ->route('manage.overview')
            ->with('success', 'Sick leave denied.');
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

    private function authorizeLeaveRequest(LeaveRequest $leaveRequest, User $manager): void
    {
        abort_unless($leaveRequest->company_id === $manager->company_id, 403);
        abort_unless($leaveRequest->status === LeaveRequestStatus::Pending, 403);
    }

    private function authorizeSickLeave(SickLeave $sickLeave, User $manager): void
    {
        abort_unless($sickLeave->company_id === $manager->company_id, 403);
        abort_unless($sickLeave->status === SickLeaveStatus::Reported, 403);
    }

}
