<?php

namespace Database\Factories;

use App\Enums\EventStatus;
use App\Models\Category;
use App\Models\Event;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Event>
 */
class EventFactory extends Factory
{
    public function definition(): array
    {
        $title = fake()->unique()->sentence(4);
        $start = fake()->dateTimeBetween('+1 week', '+3 months');
        $end = (clone $start)->modify('+3 hours');
        $regStart = (clone $start)->modify('-2 weeks');
        $regEnd = (clone $start)->modify('-1 hour');

        return [
            'organizer_id' => User::factory()->organizer(),
            'category_id' => Category::factory(),
            'title' => $title,
            'slug' => Str::slug($title).'-'.fake()->unique()->numberBetween(1000, 9999),
            'description' => fake()->paragraphs(3, true),
            'poster_path' => null,
            'format' => fake()->randomElement(['online', 'offline', 'hybrid']),
            'location' => fake()->city(),
            'online_url' => fake()->url(),
            'start_at' => $start,
            'end_at' => $end,
            'registration_start_at' => $regStart,
            'registration_end_at' => $regEnd,
            'status' => EventStatus::Draft,
            'is_disabled' => false,
            'published_at' => null,
            'cancelled_at' => null,
        ];
    }

    public function published(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => EventStatus::Published,
            'published_at' => now(),
        ]);
    }

    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => EventStatus::Cancelled,
            'cancelled_at' => now(),
        ]);
    }
}
