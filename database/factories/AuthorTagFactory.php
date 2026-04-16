<?php

namespace Database\Factories;

use App\Models\Author;
use App\Models\AuthorTag;
use App\Models\Tag;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AuthorTag>
 */
class AuthorTagFactory extends Factory
{
    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'author_id' => Author::factory(),
            'tag_id' => Tag::factory(),
        ];
    }
}
