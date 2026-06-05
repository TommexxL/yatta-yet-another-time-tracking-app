<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\Schedule;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Schedule>
 */
class ScheduleFactory extends Factory
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
            'name' => fake()->randomElement([
                'Full-time office schedule',
                'Part-time morning schedule',
                'Flexible work schedule',
                'Operations day shift',
            ]),
            'weekly_hours' => fake()->randomElement([20, 24, 32, 38, 40]),
            'active' => true,
        ];
    }
}
