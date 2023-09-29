<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Recording>
 */
class RecordingFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = fake()->sentence;
        return [
            'title' => $title,
            'description' => fake()->paragraph,
            'file_location' => 'storage/videos/' . Str::random(20) . '_video.mp4',
            'file_name' => fake()->word . '.mp4',
            'file_size' => fake()->numberBetween(100000, 20480), // Adjust the range as needed
            'slug' => Str::slug($title),
            'thumbnail' => fake()->imageUrl
        ];
    }
}
