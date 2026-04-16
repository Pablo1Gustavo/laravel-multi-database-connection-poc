<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\Author;
use App\Models\AuthorTag;
use App\Models\Comment;
use App\Models\Label;
use App\Models\Labelable;
use App\Models\Profile;
use App\Models\Sticker;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);
        User::factory()->count(99)->create();

        $tags = Tag::factory()->count(100)->create();
        $labels = Label::factory()->count(100)->create();

        $activeAuthors = Author::factory()->count(90)->create();
        $deletedAuthors = Author::factory()->count(10)->create();
        $deletedAuthors->each->delete();
        $authors = $activeAuthors->merge($deletedAuthors);

        $activeAuthors->random(80)->each(
            fn (Author $author) => Profile::factory()->create(['author_id' => $author->id])
        );

        $articles = Article::factory()
            ->count(100)
            ->state(fn () => [
                'author_id' => $authors->random()->id,
                'published' => fake()->boolean(70),
            ])
            ->create();
        $articles->random(8)->each->delete();

        Comment::factory()
            ->count(120)
            ->state(fn () => ['article_id' => $articles->random()->id])
            ->create();

        Sticker::factory()
            ->count(100)
            ->state(fn () => [
                'stickerable_type' => Author::class,
                'stickerable_id' => $authors->random()->id,
            ])
            ->create();

        foreach ($authors as $author) {
            $count = fake()->numberBetween(0, 3);

            if ($count === 0) {
                continue;
            }

            foreach ($tags->random($count) as $tag) {
                AuthorTag::factory()->create([
                    'author_id' => $author->id,
                    'tag_id' => $tag->id,
                ]);
            }
        }

        Labelable::factory()
            ->count(70)
            ->state(fn () => [
                'label_id' => $labels->random()->id,
                'labelable_type' => Author::class,
                'labelable_id' => $authors->random()->id,
            ])
            ->create();

        Labelable::factory()
            ->count(30)
            ->state(fn () => [
                'label_id' => $labels->random()->id,
                'labelable_type' => Article::class,
                'labelable_id' => $articles->random()->id,
            ])
            ->create();
    }
}
