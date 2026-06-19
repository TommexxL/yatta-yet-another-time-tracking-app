<?php

namespace Tests\Feature;

use App\Models\Schedule;
use App\Models\ScheduleDay;
use App\Models\LeaveRequest;
use App\Models\SickLeave;
use App\Models\TimeEntry;
use App\Models\User;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ScheduleOverviewTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_view_current_week_schedule_with_time_entries(): void
    {
        $user = User::factory()->create();
        $schedule = Schedule::factory()->create([
            'company_id' => $user->company_id,
            'name' => 'Standard Week',
            'weekly_hours' => 40,
        ]);
        $user->schedules()->attach($schedule->id, [
            'starts_at' => now()->startOfYear()->toDateString(),
            'ends_at' => null,
        ]);

        ScheduleDay::factory()->create([
            'schedule_id' => $schedule->id,
            'weekday' => 1,
            'start_time' => '08:00:00',
            'end_time' => '17:00:00',
            'break_minutes' => 60,
        ]);

        $monday = CarbonImmutable::now()->startOfWeek(CarbonInterface::MONDAY);
        TimeEntry::factory()->create([
            'company_id' => $user->company_id,
            'user_id' => $user->id,
            'date' => $monday->toDateString(),
            'clock_in' => '08:03:00',
            'clock_out' => '17:04:00',
            'status' => 'approved',
        ]);

        $this->actingAs($user)
            ->get('/schedule')
            ->assertOk()
            ->assertSee('Standard Week')
            ->assertSee('08:00')
            ->assertSee('17:00')
            ->assertSee('08:03')
            ->assertSee('17:04')
            ->assertSee('Request Change')
            ->assertSee('Vacation')
            ->assertSee('Sick');
    }

    public function test_leave_request_form_defaults_to_selected_schedule_day(): void
    {
        $user = User::factory()->create();
        $date = CarbonImmutable::now()->startOfWeek(CarbonInterface::MONDAY);

        $this->actingAs($user)
            ->get(route('leave-request.create', [
                'date' => $date->toDateString(),
                'type' => 'sick',
            ]))
            ->assertOk()
            ->assertSee('Request Leave')
            ->assertSee('value="'.$date->toDateString().'"', false)
            ->assertSee('Sick leave');
    }

    public function test_approved_vacation_replaces_scheduled_hours_on_schedule(): void
    {
        $user = User::factory()->create();
        $schedule = Schedule::factory()->create([
            'company_id' => $user->company_id,
            'name' => 'Standard Week',
        ]);
        $user->schedules()->attach($schedule->id, [
            'starts_at' => now()->startOfYear()->toDateString(),
            'ends_at' => null,
        ]);

        ScheduleDay::factory()->create([
            'schedule_id' => $schedule->id,
            'weekday' => 1,
            'start_time' => '08:00:00',
            'end_time' => '17:00:00',
        ]);

        $monday = CarbonImmutable::now()->startOfWeek(CarbonInterface::MONDAY);
        LeaveRequest::factory()->create([
            'company_id' => $user->company_id,
            'user_id' => $user->id,
            'leave_type' => 'vacation',
            'start_date' => $monday->toDateString(),
            'end_date' => $monday->toDateString(),
            'status' => 'approved',
        ]);

        $this->actingAs($user)
            ->get(route('schedule'))
            ->assertOk()
            ->assertSee('Vacation')
            ->assertSee('Vacation approved')
            ->assertDontSee('08:00');
    }

    public function test_approved_sick_leave_replaces_scheduled_hours_on_schedule(): void
    {
        $user = User::factory()->create();
        $schedule = Schedule::factory()->create([
            'company_id' => $user->company_id,
            'name' => 'Standard Week',
        ]);
        $user->schedules()->attach($schedule->id, [
            'starts_at' => now()->startOfYear()->toDateString(),
            'ends_at' => null,
        ]);

        ScheduleDay::factory()->create([
            'schedule_id' => $schedule->id,
            'weekday' => 1,
            'start_time' => '08:00:00',
            'end_time' => '17:00:00',
        ]);

        $monday = CarbonImmutable::now()->startOfWeek(CarbonInterface::MONDAY);
        SickLeave::factory()->create([
            'company_id' => $user->company_id,
            'user_id' => $user->id,
            'start_date' => $monday->toDateString(),
            'expected_return_date' => $monday->toDateString(),
            'status' => 'approved',
        ]);

        $this->actingAs($user)
            ->get(route('schedule'))
            ->assertOk()
            ->assertSee('Sick')
            ->assertSee('Sick leave approved')
            ->assertDontSee('08:00');
    }

    public function test_authenticated_user_can_request_vacation_for_longer_period(): void
    {
        $user = User::factory()->create();
        $startDate = CarbonImmutable::now()->startOfWeek(CarbonInterface::MONDAY);
        $endDate = $startDate->addDays(2);

        $this->actingAs($user)
            ->post(route('leave-request.store'), [
                'type' => 'vacation',
                'start_date' => $startDate->toDateString(),
                'end_date' => $endDate->toDateString(),
                'reason' => 'Family trip.',
            ])
            ->assertRedirect(route('schedule'));

        $this->assertDatabaseHas('leave_requests', [
            'company_id' => $user->company_id,
            'user_id' => $user->id,
            'leave_type' => 'vacation',
            'start_date' => $startDate->startOfDay()->toDateTimeString(),
            'end_date' => $endDate->startOfDay()->toDateTimeString(),
            'days' => 3,
            'reason' => 'Family trip.',
            'status' => 'pending',
        ]);
    }

    public function test_authenticated_user_can_request_sick_leave(): void
    {
        $user = User::factory()->create();
        $startDate = CarbonImmutable::now()->startOfWeek(CarbonInterface::MONDAY);
        $endDate = $startDate->addDay();

        $this->actingAs($user)
            ->post(route('leave-request.store'), [
                'type' => 'sick',
                'start_date' => $startDate->toDateString(),
                'end_date' => $endDate->toDateString(),
                'reason' => 'Flu symptoms.',
            ])
            ->assertRedirect(route('schedule'));

        $this->assertDatabaseHas('sick_leaves', [
            'company_id' => $user->company_id,
            'user_id' => $user->id,
            'start_date' => $startDate->startOfDay()->toDateTimeString(),
            'expected_return_date' => $endDate->startOfDay()->toDateTimeString(),
            'notes' => 'Flu symptoms.',
            'status' => 'reported',
        ]);
    }

    public function test_authenticated_user_can_request_time_entry_correction(): void
    {
        $user = User::factory()->create();
        $date = CarbonImmutable::now()->startOfWeek(CarbonInterface::MONDAY);
        $entry = TimeEntry::factory()->create([
            'company_id' => $user->company_id,
            'user_id' => $user->id,
            'date' => $date->toDateString(),
            'clock_in' => '08:03:00',
            'clock_out' => '17:04:00',
            'status' => 'approved',
        ]);

        $this->actingAs($user)
            ->post(route('time-entry.correction.store', ['date' => $date->toDateString()]), [
                'requested_clock_in' => '08:00',
                'requested_clock_out' => '17:00',
                'reason' => 'Forgot to correct the exact clock times.',
            ])
            ->assertRedirect(route('schedule'));

        $this->assertDatabaseHas('time_entry_corrections', [
            'company_id' => $user->company_id,
            'user_id' => $user->id,
            'time_entry_id' => $entry->id,
            'date' => $date->startOfDay()->toDateTimeString(),
            'current_clock_in' => '08:03:00',
            'current_clock_out' => '17:04:00',
            'requested_clock_in' => '08:00',
            'requested_clock_out' => '17:00',
            'status' => 'pending',
        ]);
    }
}
