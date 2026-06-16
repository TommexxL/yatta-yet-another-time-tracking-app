<?php

namespace Database\Factories;

use App\Enums\TimeEntryCorrectionStatus;
use App\Models\Company;
use App\Models\TimeEntry;
use App\Models\TimeEntryCorrection;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TimeEntryCorrection>
 */
class TimeEntryCorrectionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'company_id' => Company::factory(),
            'user_id' => User::factory(),
            'time_entry_id' => TimeEntry::factory(),
            'date' => fake()->dateTimeBetween('-14 days', 'today')->format('Y-m-d'),
            'current_clock_in' => fake()->optional()->randomElement(['08:00:00', '08:15:00', '09:00:00']),
            'current_clock_out' => fake()->optional()->randomElement(['16:45:00', '17:00:00', '17:30:00']),
            'requested_clock_in' => fake()->randomElement(['08:00:00', '08:15:00', '09:00:00']),
            'requested_clock_out' => fake()->randomElement(['16:45:00', '17:00:00', '17:30:00']),
            'reason' => fake()->sentence(),
            'status' => TimeEntryCorrectionStatus::Pending,
        ];
    }
}
