<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Task>
 */
class TaskFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => fn() => User::factory()->create()->id,
            'title' => Str::random(4),
            'description' => Str::random(8),
            'expires_at' => fake()->dateTimeBetween(startDate: 'now', endDate: '+1 week', timezone: null),
        ];
    }

    public function withUser(User $user): self
    {
        return $this->state(
            [
                'user_id' => $user->id,
            ]
        );
    }
}
