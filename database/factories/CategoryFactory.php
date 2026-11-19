<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Category>
 */
class CategoryFactory extends Factory
{
    public function definition(): array
    {
        $name = fake()->unique()->randomElement([
            'Musik', 'Teknologi', 'Olahraga', 'Seni & Budaya', 'Bisnis',
            'Pendidikan', 'Kesehatan', 'Komunitas', 'Gaming', 'Film',
        ]).' '.fake()->unique()->numberBetween(1, 9999);

        return [
            'name' => $name,
            'slug' => Str::slug($name),
        ];
    }
}
