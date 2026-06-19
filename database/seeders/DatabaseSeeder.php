<?php

namespace Database\Seeders;

use App\Enums\LeaveRequestStatus;
use App\Models\AuditLog;
use App\Models\Company;
use App\Models\LeaveRequest;
use App\Models\Schedule;
use App\Models\ScheduleDay;
use App\Models\SickLeave;
use App\Models\TimeEntry;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $adminRole = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $managerRole = Role::firstOrCreate(['name' => 'manager', 'guard_name' => 'web']);
        $employeeRole = Role::firstOrCreate(['name' => 'employee', 'guard_name' => 'web']);

        $company = Company::factory()->create([
            'name' => 'Yatta Demo Company',
            'email' => 'hello@yatta.test',
            'phone' => '+32 3 555 01 00',
            'address' => 'Demo Street 12, 2000 Antwerp',
        ]);

        Company::factory()->create([
            'name' => 'Inactive Sandbox Ltd',
            'active' => false,
        ]);

        $schedule = Schedule::factory()->create([
            'company_id' => $company->id,
            'name' => 'Standard 40h week',
            'weekly_hours' => 40,
        ]);

        foreach (range(1, 5) as $weekday) {
            ScheduleDay::factory()->create([
                'schedule_id' => $schedule->id,
                'weekday' => $weekday,
                'start_time' => '08:00:00',
                'end_time' => '17:00:00',
                'break_minutes' => 60,
            ]);
        }

        $admin = User::factory()->create([
            'company_id' => $company->id,
            'name' => 'Alex Admin',
            'first_name' => 'Alex',
            'last_name' => 'Admin',
            'email' => 'admin@yatta.test',
            'employee_number' => 'EMP-0001',
            'department' => 'Administration',
            'active' => true,
        ]);
        $admin->assignRole($adminRole);

        $managers = User::factory()
            ->count(2)
            ->sequence(
                [
                    'name' => 'Mila Manager',
                    'first_name' => 'Mila',
                    'last_name' => 'Manager',
                    'email' => 'manager@yatta.test',
                    'employee_number' => 'EMP-0002',
                    'department' => 'Operations',
                ],
                [
                    'name' => 'Noah Teamlead',
                    'first_name' => 'Noah',
                    'last_name' => 'Teamlead',
                    'email' => 'teamlead@yatta.test',
                    'employee_number' => 'EMP-0003',
                    'department' => 'Customer Support',
                ],
            )
            ->create(['company_id' => $company->id]);

        $managers->each(fn (User $manager) => $manager->assignRole($managerRole));

        $demoEmployee = User::factory()->create([
            'company_id' => $company->id,
            'name' => 'Ella Employee',
            'first_name' => 'Ella',
            'last_name' => 'Employee',
            'email' => 'employee@yatta.test',
            'employee_number' => 'EMP-0004',
            'department' => 'Operations',
            'active' => true,
        ]);
        $demoEmployee->assignRole($employeeRole);

        $employees = User::factory()
            ->count(12)
            ->create(['company_id' => $company->id]);

        $employees->each(fn (User $employee) => $employee->assignRole($employeeRole));

        $users = collect([$admin])->merge($managers)->push($demoEmployee)->merge($employees);

        $users->each(function (User $user) use ($schedule): void {
            $user->schedules()->attach($schedule->id, [
                'starts_at' => now()->startOfYear()->toDateString(),
                'ends_at' => null,
            ]);
        });

        $workdays = collect();
        $date = CarbonImmutable::now()->subDays(21);

        while ($date->lessThanOrEqualTo(CarbonImmutable::now())) {
            if ($date->isWeekday()) {
                $workdays->push($date);
            }

            $date = $date->addDay();
        }

        $users->skip(1)->each(function (User $user) use ($company, $workdays): void {
            $workdays->each(function (CarbonImmutable $date) use ($company, $user): void {
                TimeEntry::factory()->create([
                    'company_id' => $company->id,
                    'user_id' => $user->id,
                    'date' => $date->toDateString(),
                    'clock_in' => fake()->randomElement(['07:55:00', '08:00:00', '08:07:00', '08:15:00']),
                    'clock_out' => fake()->randomElement(['16:45:00', '17:00:00', '17:10:00', '17:25:00']),
                    'status' => $date->isSameDay(CarbonImmutable::now()) ? 'open' : 'approved',
                    'notes' => fake()->optional(0.1)->sentence(),
                ]);
            });
        });

        $approver = $managers->first();

        collect([$demoEmployee])->merge($employees)->take(8)->each(function (User $employee, int $index) use ($approver, $company): void {
            $status = [
                LeaveRequestStatus::Pending,
                LeaveRequestStatus::Approved,
                LeaveRequestStatus::Denied,
            ][$index % 3];

            LeaveRequest::factory()->create([
                'company_id' => $company->id,
                'user_id' => $employee->id,
                'status' => $status,
                'approved_by' => $status === 'pending' ? null : $approver->id,
                'approved_at' => $status === 'pending' ? null : now()->subDays(fake()->numberBetween(1, 10)),
                'rejection_reason' => $status === 'rejected' ? 'Insufficient remaining leave balance.' : null,
            ]);
        });

        $employees->slice(7, 3)->each(function (User $employee) use ($company): void {
            SickLeave::factory()->create([
                'company_id' => $company->id,
                'user_id' => $employee->id,
            ]);
        });

        $users->take(10)->each(function (User $user) use ($company): void {
            AuditLog::factory()->create([
                'company_id' => $company->id,
                'user_id' => $user->id,
                'action' => 'user.seeded',
                'description' => "Seeded demo user {$user->name}.",
                'auditable_type' => User::class,
                'auditable_id' => $user->id,
            ]);
        });
    }
}
