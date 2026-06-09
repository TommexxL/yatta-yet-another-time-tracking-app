<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\LeaveRequest;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<LeaveRequest>
 */
class LeaveRequestFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startDate = fake()->dateTimeBetween('-30 days', '+90 days');
        $endDate = fake()->dateTimeBetween($startDate, $startDate->format('Y-m-d').' +5 days');
        $days = max(1, $startDate->diff($endDate)->days + 1);

        return [
            'company_id' => Company::factory(),
            'user_id' => User::factory(),
            'leave_type' => fake()->randomElement(['vacation', 'unpaid', 'parental', 'training']),
            'start_date' => $startDate->format('Y-m-d'),
            'end_date' => $endDate->format('Y-m-d'),
            'days' => $days,
            'reason' => fake()->optional(0.8)->sentence(),
            'status' => fake()->randomElement(LeaveRequestStatus::cases()),
            'approved_by' => null,
            'approved_at' => null,
            'rejection_reason' => null,
        ];
    }
}
