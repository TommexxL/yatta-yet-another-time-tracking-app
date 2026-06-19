<?php

namespace Tests\Feature;

use App\Enums\TimeEntryCorrectionStatus;
use App\Models\Schedule;
use App\Models\ScheduleDay;
use App\Models\TimeEntry;
use App\Models\TimeEntryCorrection;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ManageOverviewTest extends TestCase
{
    use RefreshDatabase;

    public function test_manager_can_view_pending_time_entry_corrections(): void
    {
        $manager = $this->managerUser();
        $employee = User::factory()->create(['company_id' => $manager->company_id]);
        TimeEntryCorrection::factory()->create([
            'company_id' => $manager->company_id,
            'user_id' => $employee->id,
            'time_entry_id' => null,
            'date' => now()->toDateString(),
            'reason' => 'Forgot to clock out.',
            'status' => TimeEntryCorrectionStatus::Pending,
        ]);

        $this->actingAs($manager)
            ->get(route('manage.overview'))
            ->assertOk()
            ->assertSee('Time Corrections')
            ->assertSee($employee->name)
            ->assertSee('Forgot to clock out.');
    }

    public function test_non_manager_cannot_access_manager_overview(): void
    {
        $employee = User::factory()->create();

        $this->actingAs($employee)
            ->get(route('manage.overview'))
            ->assertForbidden();
    }

    public function test_manager_can_approve_time_entry_correction(): void
    {
        $manager = $this->managerUser();
        $employee = User::factory()->create(['company_id' => $manager->company_id]);
        $entry = TimeEntry::factory()->create([
            'company_id' => $manager->company_id,
            'user_id' => $employee->id,
            'clock_in' => '08:07:00',
            'clock_out' => '17:14:00',
            'status' => 'approved',
        ]);
        $correction = TimeEntryCorrection::factory()->create([
            'company_id' => $manager->company_id,
            'user_id' => $employee->id,
            'time_entry_id' => $entry->id,
            'date' => $entry->date->toDateString(),
            'current_clock_in' => '08:07:00',
            'current_clock_out' => '17:14:00',
            'requested_clock_in' => '08:00:00',
            'requested_clock_out' => '17:00:00',
            'status' => TimeEntryCorrectionStatus::Pending,
        ]);

        $this->actingAs($manager)
            ->post(route('manage.time-entry-corrections.approve', $correction), [
                'manager_notes' => 'Looks correct.',
            ])
            ->assertRedirect(route('manage.overview'));

        $this->assertDatabaseHas('time_entries', [
            'id' => $entry->id,
            'clock_in' => '08:00:00',
            'clock_out' => '17:00:00',
            'status' => 'corrected',
        ]);

        $this->assertDatabaseHas('time_entry_corrections', [
            'id' => $correction->id,
            'status' => 'approved',
            'reviewed_by' => $manager->id,
            'manager_notes' => 'Looks correct.',
        ]);
    }

    public function test_manager_can_set_employee_schedule(): void
    {
        $manager = $this->managerUser();
        $employee = User::factory()->create(['company_id' => $manager->company_id]);
        $oldSchedule = Schedule::factory()->create(['company_id' => $manager->company_id]);
        $newSchedule = Schedule::factory()->create([
            'company_id' => $manager->company_id,
            'name' => 'Operations Day Shift',
        ]);

        $employee->schedules()->attach($oldSchedule->id, [
            'starts_at' => now()->subMonth()->toDateString(),
            'ends_at' => null,
        ]);

        $this->actingAs($manager)
            ->post(route('manage.employees.schedule', $employee), [
                'schedule_id' => $newSchedule->id,
                'starts_at' => now()->toDateString(),
                'ends_at' => null,
            ])
            ->assertRedirect(route('manage.overview'));

        $this->assertDatabaseMissing('schedule_user', [
            'user_id' => $employee->id,
            'schedule_id' => $oldSchedule->id,
        ]);

        $this->assertDatabaseHas('schedule_user', [
            'user_id' => $employee->id,
            'schedule_id' => $newSchedule->id,
            'starts_at' => now()->toDateString(),
            'ends_at' => null,
        ]);
    }

    public function test_manager_can_create_schedule_with_working_days(): void
    {
        $manager = $this->managerUser();

        $this->actingAs($manager)
            ->post(route('manage.schedules.store'), [
                'name' => 'Late Support Shift',
                'weekly_hours' => 32,
                'active' => '1',
                'days' => [
                    1 => [
                        'enabled' => '1',
                        'start_time' => '10:00',
                        'end_time' => '18:00',
                        'break_minutes' => 30,
                    ],
                    2 => [
                        'enabled' => '1',
                        'start_time' => '10:00',
                        'end_time' => '18:00',
                        'break_minutes' => 30,
                    ],
                ],
            ])
            ->assertRedirect(route('manage.overview'));

        $this->assertDatabaseHas('schedules', [
            'company_id' => $manager->company_id,
            'name' => 'Late Support Shift',
            'weekly_hours' => 32,
            'active' => true,
        ]);

        $schedule = Schedule::where('name', 'Late Support Shift')->firstOrFail();

        $this->assertDatabaseHas('schedule_days', [
            'schedule_id' => $schedule->id,
            'weekday' => 1,
            'start_time' => '10:00',
            'end_time' => '18:00',
            'break_minutes' => 30,
        ]);

        $this->assertDatabaseCount('schedule_days', 2);
    }

    public function test_manager_can_update_own_company_schedule(): void
    {
        $manager = $this->managerUser();
        $schedule = Schedule::factory()->create([
            'company_id' => $manager->company_id,
            'name' => 'Old Shift',
        ]);
        ScheduleDay::factory()->create([
            'schedule_id' => $schedule->id,
            'weekday' => 1,
            'start_time' => '09:00',
            'end_time' => '17:00',
        ]);

        $this->actingAs($manager)
            ->put(route('manage.schedules.update', $schedule), [
                'name' => 'Updated Shift',
                'weekly_hours' => 24,
                'active' => '0',
                'days' => [
                    3 => [
                        'enabled' => '1',
                        'start_time' => '08:30',
                        'end_time' => '14:30',
                        'break_minutes' => 15,
                    ],
                ],
            ])
            ->assertRedirect(route('manage.overview'));

        $this->assertDatabaseHas('schedules', [
            'id' => $schedule->id,
            'name' => 'Updated Shift',
            'weekly_hours' => 24,
            'active' => false,
        ]);

        $this->assertDatabaseMissing('schedule_days', [
            'schedule_id' => $schedule->id,
            'weekday' => 1,
        ]);

        $this->assertDatabaseHas('schedule_days', [
            'schedule_id' => $schedule->id,
            'weekday' => 3,
            'start_time' => '08:30',
            'end_time' => '14:30',
            'break_minutes' => 15,
        ]);
    }

    public function test_manager_can_remove_own_company_schedule(): void
    {
        $manager = $this->managerUser();
        $employee = User::factory()->create(['company_id' => $manager->company_id]);
        $schedule = Schedule::factory()->create(['company_id' => $manager->company_id]);

        $employee->schedules()->attach($schedule->id, [
            'starts_at' => now()->toDateString(),
            'ends_at' => null,
        ]);

        $this->actingAs($manager)
            ->delete(route('manage.schedules.destroy', $schedule))
            ->assertRedirect(route('manage.overview'));

        $this->assertDatabaseMissing('schedules', [
            'id' => $schedule->id,
        ]);

        $this->assertDatabaseMissing('schedule_user', [
            'schedule_id' => $schedule->id,
            'user_id' => $employee->id,
        ]);
    }

    public function test_manager_cannot_update_schedule_from_another_company(): void
    {
        $manager = $this->managerUser();
        $schedule = Schedule::factory()->create();

        $this->actingAs($manager)
            ->put(route('manage.schedules.update', $schedule), [
                'name' => 'Cross Company Shift',
                'weekly_hours' => 40,
                'active' => '1',
                'days' => [
                    1 => [
                        'enabled' => '1',
                        'start_time' => '09:00',
                        'end_time' => '17:00',
                        'break_minutes' => 30,
                    ],
                ],
            ])
            ->assertForbidden();
    }

    private function managerUser(): User
    {
        $managerRole = Role::firstOrCreate(['name' => 'manager', 'guard_name' => 'web']);
        $manager = User::factory()->create();
        $manager->assignRole($managerRole);

        return $manager;
    }
}
