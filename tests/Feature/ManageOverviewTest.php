<?php

namespace Tests\Feature;

use App\Enums\TimeEntryCorrectionStatus;
use App\Models\Schedule;
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

    private function managerUser(): User
    {
        $managerRole = Role::firstOrCreate(['name' => 'manager', 'guard_name' => 'web']);
        $manager = User::factory()->create();
        $manager->assignRole($managerRole);

        return $manager;
    }
}
