<?php

namespace Database\Factories;

use App\Models\Theme;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Post>
 */
class PostFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => fake()->numberBetween(1, 10),
            'title' => fake()->unique()->sentence(5, 4),
            'summary' => fake()->paragraph(3),
            'body' => fake()->realTextBetween(200, 1000, 1),
            'published_at' => fake()->dateTimeBetween('-1 year', 'now'),
            'theme_id' => Theme::inRandomOrder()->first()->id,
        ];
    }
}
