<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\TimeEntry;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TimeEntry>
 */
class TimeEntryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $clockIn = fake()->dateTimeBetween('08:00', '09:30')->format('H:i:s');
        $clockOut = fake()->dateTimeBetween('16:30', '18:00')->format('H:i:s');

        return [
            'company_id' => Company::factory(),
            'user_id' => User::factory(),
            'date' => fake()->dateTimeBetween('-60 days', 'today')->format('Y-m-d'),
            'clock_in' => $clockIn,
            'clock_out' => $clockOut,
            'status' => fake()->randomElement(['open', 'submitted', 'approved', 'corrected']),
            'notes' => fake()->optional(0.2)->sentence(),
        ];
    }
}
