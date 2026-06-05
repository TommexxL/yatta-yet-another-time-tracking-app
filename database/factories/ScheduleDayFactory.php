<?php

namespace Database\Factories;

use App\Models\Schedule;
use App\Models\ScheduleDay;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ScheduleDay>
 */
class ScheduleDayFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startTime = fake()->randomElement(['08:00', '08:30', '09:00']);

        return [
            'schedule_id' => Schedule::factory(),
            'weekday' => fake()->numberBetween(1, 5),
            'start_time' => $startTime,
            'end_time' => fake()->randomElement(['16:30', '17:00', '17:30']),
            'break_minutes' => fake()->randomElement([30, 45, 60]),
        ];
    }
}
