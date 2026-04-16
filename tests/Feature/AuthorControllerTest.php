<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\Author;
use App\Models\AuthorTag;
use App\Models\Comment;
use App\Models\Label;
use App\Models\Labelable;
use App\Models\Profile;
use App\Models\Sticker;
use App\Models\Tag;
use Tests\TestCase;

class AuthorControllerTest extends TestCase
{
    public function test_index_returns_authors(): void
    {
        Author::factory()->count(3)->create();

        $response = $this->getJson('/api/authors');

        $response->assertOk()
            ->assertJsonCount(3);
    }

    public function test_index_filters_authors_with_published_articles(): void
    {
        $authorWithPublished = Author::factory()->create();
        Article::factory()->published()->create(['author_id' => $authorWithPublished->id]);

        $authorWithDraft = Author::factory()->create();
        Article::factory()->create(['author_id' => $authorWithDraft->id, 'published' => false]);

        Author::factory()->create();

        $response = $this->getJson('/api/authors?has_published_articles=1');

        $response->assertOk()
            ->assertJsonCount(1)
            ->assertJsonPath('0.id', $authorWithPublished->id);
    }

    public function test_index_filters_authors_with_minimum_articles(): void
    {
        $prolificAuthor = Author::factory()->create();
        Article::factory()->count(3)->create(['author_id' => $prolificAuthor->id]);

        $casualAuthor = Author::factory()->create();
        Article::factory()->create(['author_id' => $casualAuthor->id]);

        $response = $this->getJson('/api/authors?min_articles=2');

        $response->assertOk()
            ->assertJsonCount(1)
            ->assertJsonPath('0.id', $prolificAuthor->id);
    }

    public function test_index_filters_authors_without_articles(): void
    {
        $authorWithArticles = Author::factory()->create();
        Article::factory()->create(['author_id' => $authorWithArticles->id]);

        $authorWithoutArticles = Author::factory()->create();

        $response = $this->getJson('/api/authors?without_articles=1');

        $response->assertOk()
            ->assertJsonCount(1)
            ->assertJsonPath('0.id', $authorWithoutArticles->id);
    }

    public function test_index_filters_authors_with_comments_through_articles(): void
    {
        $authorWithComments = Author::factory()->create();
        $article = Article::factory()->create(['author_id' => $authorWithComments->id]);
        Comment::factory()->create(['article_id' => $article->id]);

        $authorWithoutComments = Author::factory()->create();
        Article::factory()->create(['author_id' => $authorWithoutComments->id]);

        $response = $this->getJson('/api/authors?has_comments=1');

        $response->assertOk()
            ->assertJsonCount(1)
            ->assertJsonPath('0.id', $authorWithComments->id);
    }

    public function test_index_filters_authors_with_stickers(): void
    {
        $authorWithStickers = Author::factory()->create();
        Sticker::factory()->create([
            'stickerable_type' => Author::class,
            'stickerable_id' => $authorWithStickers->id,
        ]);

        Author::factory()->create();

        $response = $this->getJson('/api/authors?has_stickers=1');

        $response->assertOk()
            ->assertJsonCount(1)
            ->assertJsonPath('0.id', $authorWithStickers->id);
    }

    public function test_index_filters_authors_by_tag_name(): void
    {
        $tag = Tag::factory()->create(['name' => 'laravel']);
        $taggedAuthor = Author::factory()->create();
        AuthorTag::factory()->create(['author_id' => $taggedAuthor->id, 'tag_id' => $tag->id]);

        Author::factory()->create();

        $response = $this->getJson('/api/authors?tag_name=laravel');

        $response->assertOk()
            ->assertJsonCount(1)
            ->assertJsonPath('0.id', $taggedAuthor->id);
    }

    public function test_index_filters_authors_with_profile(): void
    {
        $authorWithProfile = Author::factory()->create();
        Profile::factory()->create(['author_id' => $authorWithProfile->id]);

        Author::factory()->create();

        $response = $this->getJson('/api/authors?has_profile=1');

        $response->assertOk()
            ->assertJsonCount(1)
            ->assertJsonPath('0.id', $authorWithProfile->id);
    }

    public function test_index_sorts_by_articles_count(): void
    {
        $prolificAuthor = Author::factory()->create();
        Article::factory()->count(5)->create(['author_id' => $prolificAuthor->id]);

        $casualAuthor = Author::factory()->create();
        Article::factory()->create(['author_id' => $casualAuthor->id]);

        $response = $this->getJson('/api/authors?sort=articles_count');

        $response->assertOk()
            ->assertJsonPath('0.id', $prolificAuthor->id);
    }

    public function test_store_creates_author(): void
    {
        $response = $this->postJson('/api/authors', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'bio' => 'A writer',
        ]);

        $response->assertCreated()
            ->assertJsonPath('name', 'John Doe');

        $this->assertDatabaseHas('authors', ['email' => 'john@example.com'], 'primary');
    }

    public function test_store_validates_required_fields(): void
    {
        $response = $this->postJson('/api/authors', []);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['name', 'email']);
    }

    public function test_store_validates_unique_email(): void
    {
        Author::factory()->create(['email' => 'taken@example.com']);

        $response = $this->postJson('/api/authors', [
            'name' => 'Jane Doe',
            'email' => 'taken@example.com',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['email']);
    }

    public function test_show_returns_author_with_relationships_and_counts(): void
    {
        $author = Author::factory()->create();
        $article = Article::factory()->published()->create(['author_id' => $author->id]);
        Article::factory()->create(['author_id' => $author->id, 'published' => false]);
        Comment::factory()->count(2)->create(['article_id' => $article->id]);
        Profile::factory()->create(['author_id' => $author->id]);
        $tag = Tag::factory()->create();
        AuthorTag::factory()->create(['author_id' => $author->id, 'tag_id' => $tag->id]);
        Sticker::factory()->create([
            'stickerable_type' => Author::class,
            'stickerable_id' => $author->id,
        ]);
        $label = Label::factory()->create();
        Labelable::factory()->create([
            'label_id' => $label->id,
            'labelable_type' => Author::class,
            'labelable_id' => $author->id,
        ]);

        $response = $this->getJson("/api/authors/{$author->id}");

        $response->assertOk()
            ->assertJsonPath('id', $author->id)
            ->assertJsonPath('articles_count', 2)
            ->assertJsonPath('published_articles_count', 1)
            ->assertJsonPath('comments_through_articles_count', 2)
            ->assertJsonPath('stickers_count', 1);
    }

    public function test_update_modifies_author(): void
    {
        $author = Author::factory()->create();

        $response = $this->putJson("/api/authors/{$author->id}", [
            'name' => 'Updated Name',
        ]);

        $response->assertOk()
            ->assertJsonPath('name', 'Updated Name');
    }

    public function test_destroy_soft_deletes_author(): void
    {
        $author = Author::factory()->create();

        $response = $this->deleteJson("/api/authors/{$author->id}");

        $response->assertNoContent();
        $this->assertSoftDeleted('authors', ['id' => $author->id], 'primary');
    }
}
