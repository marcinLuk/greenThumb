<?php

namespace Database\Factories;

use App\Models\JournalEntry;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\JournalEntry>
 */
class JournalEntryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'title' => fake()->sentence(),
            'content' => fake()->paragraphs(3, true),
            'entry_date' => fake()->dateTimeBetween('-1 year', 'today')->format('Y-m-d'),
        ];
    }

    /**
     * Create an entry for a specific date.
     */
    public function forDate(string $date): static
    {
        return $this->state(fn (array $attributes) => [
            'entry_date' => $date,
        ]);
    }

    /**
     * Create an entry for a specific user.
     */
    public function forUser(User $user): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => $user->id,
        ]);
    }

    /**
     * Create an entry for today.
     */
    public function today(): static
    {
        return $this->state(fn (array $attributes) => [
            'entry_date' => now()->format('Y-m-d'),
        ]);
    }

    /**
     * Create an entry for a random past date.
     */
    public function pastDate(): static
    {
        return $this->state(fn (array $attributes) => [
            'entry_date' => fake()->dateTimeBetween('-2 years', '-1 day')->format('Y-m-d'),
        ]);
    }

    /**
     * Create an entry for a specific number of days ago.
     */
    public function daysAgo(int $days): static
    {
        return $this->state(fn (array $attributes) => [
            'entry_date' => now()->subDays($days)->format('Y-m-d'),
        ]);
    }
}
