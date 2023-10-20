<?php

namespace Database\Factories;

use App\Enums\ReleaseStatus;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Release>
 */
class ReleaseFactory extends Factory
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
            'slug' => Str::slug($title),
            'content' => fake()->paragraphs(4, asText: true),
            'status' => fake()->randomElement(ReleaseStatus::values())
        ];
    }
}
