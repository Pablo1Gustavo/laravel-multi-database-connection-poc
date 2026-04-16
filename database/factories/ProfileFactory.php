<?php

namespace Database\Factories;

use App\Models\Author;
use App\Models\Profile;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Profile>
 */
class ProfileFactory extends Factory
{
    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'author_id' => Author::factory(),
            'website' => fake()->optional()->url(),
            'twitter_handle' => fake()->optional()->userName(),
            'avatar_url' => fake()->optional()->imageUrl(200, 200),
        ];
    }
}
