<?php

namespace Database\Factories;

use App\Models\Author;
use App\Models\Label;
use App\Models\Labelable;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Labelable>
 */
class LabelableFactory extends Factory
{
    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'label_id' => Label::factory(),
            'labelable_type' => Author::class,
            'labelable_id' => Author::factory(),
        ];
    }
}
