<?php

namespace Database\Factories;

use App\Models\Author;
use App\Models\Sticker;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Sticker>
 */
class StickerFactory extends Factory
{
    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'name' => fake()->word(),
            'stickerable_type' => Author::class,
            'stickerable_id' => Author::factory(),
        ];
    }
}
