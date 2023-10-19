<?php

namespace Database\Factories;

use App\Enums\PostStatus;
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
            'title' => $title = fake()->words(6, true),
            'slug' => str($title)->slug(),
            'content' => fake()->paragraphs(4, asText: true),
            'status' => fake()->randomElement(PostStatus::values())
        ];
    }
}
