<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\SickLeave;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SickLeave>
 */
class SickLeaveFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startDate = fake()->dateTimeBetween('-45 days', 'today');
        $isClosed = fake()->boolean(65);

        return [
            'company_id' => Company::factory(),
            'user_id' => User::factory(),
            'start_date' => $startDate->format('Y-m-d'),
            'expected_return_date' => fake()->dateTimeBetween($startDate, $startDate->format('Y-m-d').' +7 days')->format('Y-m-d'),
            'end_date' => $isClosed
                ? fake()->dateTimeBetween($startDate, $startDate->format('Y-m-d').' +7 days')->format('Y-m-d')
                : null,
            'notes' => fake()->optional(0.7)->sentence(),
            'status' => $isClosed ? 'closed' : 'reported',
        ];
    }
}
