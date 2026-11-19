<?php

namespace Database\Factories;

use App\Models\Event;
use App\Models\TicketType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TicketType>
 */
class TicketTypeFactory extends Factory
{
    public function definition(): array
    {
        return [
            'event_id' => Event::factory(),
            'name' => fake()->randomElement(['Reguler', 'VIP', 'Early Bird', 'Presale', 'Umum']),
            'description' => fake()->sentence(),
            'quota' => fake()->numberBetween(20, 200),
            'available_from' => null,
            'available_until' => null,
            'is_active' => true,
        ];
    }
}
