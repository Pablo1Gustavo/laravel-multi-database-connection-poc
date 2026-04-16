<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\Author;
use App\Models\Comment;
use Tests\TestCase;

class ArticleControllerTest extends TestCase
{
    public function test_index_returns_articles(): void
    {
        Article::factory()->count(3)->create();

        $response = $this->getJson('/api/articles');

        $response->assertOk()
            ->assertJsonCount(3);
    }

    public function test_index_filters_by_author_name_via_join(): void
    {
        $targetAuthor = Author::factory()->create(['name' => 'John Doe']);
        Article::factory()->count(2)->create(['author_id' => $targetAuthor->id]);

        $otherAuthor = Author::factory()->create(['name' => 'Jane Smith']);
        Article::factory()->create(['author_id' => $otherAuthor->id]);

        $response = $this->getJson('/api/articles?author_name=John');

        $response->assertOk()
            ->assertJsonCount(2);
    }

    public function test_index_filters_articles_with_comments(): void
    {
        $articleWithComments = Article::factory()->create();
        Comment::factory()->create(['article_id' => $articleWithComments->id]);

        Article::factory()->create();

        $response = $this->getJson('/api/articles?has_comments=1');

        $response->assertOk()
            ->assertJsonCount(1)
            ->assertJsonPath('0.id', $articleWithComments->id);
    }

    public function test_index_filters_articles_with_min_comments(): void
    {
        $popularArticle = Article::factory()->create();
        Comment::factory()->count(3)->create(['article_id' => $popularArticle->id]);

        $quietArticle = Article::factory()->create();
        Comment::factory()->create(['article_id' => $quietArticle->id]);

        $response = $this->getJson('/api/articles?min_comments=2');

        $response->assertOk()
            ->assertJsonCount(1)
            ->assertJsonPath('0.id', $popularArticle->id);
    }

    public function test_index_filters_articles_without_comments(): void
    {
        $articleWithComments = Article::factory()->create();
        Comment::factory()->create(['article_id' => $articleWithComments->id]);

        $articleWithout = Article::factory()->create();

        $response = $this->getJson('/api/articles?without_comments=1');

        $response->assertOk()
            ->assertJsonCount(1)
            ->assertJsonPath('0.id', $articleWithout->id);
    }

    public function test_index_filters_published_articles(): void
    {
        Article::factory()->published()->count(2)->create();
        Article::factory()->create(['published' => false]);

        $response = $this->getJson('/api/articles?published=1');

        $response->assertOk()
            ->assertJsonCount(2);
    }

    public function test_index_sorts_by_author_name(): void
    {
        $authorA = Author::factory()->create(['name' => 'Alice']);
        $authorZ = Author::factory()->create(['name' => 'Zack']);

        $articleByZ = Article::factory()->create(['author_id' => $authorZ->id]);
        $articleByA = Article::factory()->create(['author_id' => $authorA->id]);

        $response = $this->getJson('/api/articles?sort=author_name');

        $response->assertOk()
            ->assertJsonPath('0.id', $articleByA->id);
    }

    public function test_index_sorts_by_comments_count(): void
    {
        $popularArticle = Article::factory()->create();
        Comment::factory()->count(5)->create(['article_id' => $popularArticle->id]);

        $quietArticle = Article::factory()->create();
        Comment::factory()->create(['article_id' => $quietArticle->id]);

        $response = $this->getJson('/api/articles?sort=comments_count');

        $response->assertOk()
            ->assertJsonPath('0.id', $popularArticle->id);
    }

    public function test_store_creates_article(): void
    {
        $author = Author::factory()->create();

        $response = $this->postJson('/api/articles', [
            'author_id' => $author->id,
            'title' => 'Test Article',
            'body' => 'Article body content',
            'published' => true,
        ]);

        $response->assertCreated()
            ->assertJsonPath('title', 'Test Article')
            ->assertJsonPath('author_id', $author->id);

        $this->assertDatabaseHas('articles', ['title' => 'Test Article'], 'secondary');
    }

    public function test_store_validates_required_fields(): void
    {
        $response = $this->postJson('/api/articles', []);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['author_id', 'title']);
    }

    public function test_store_validates_author_exists_on_primary(): void
    {
        $response = $this->postJson('/api/articles', [
            'author_id' => 999,
            'title' => 'Orphan Article',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['author_id']);
    }

    public function test_show_returns_article_with_relationships(): void
    {
        $author = Author::factory()->create();
        $article = Article::factory()->create(['author_id' => $author->id]);
        Comment::factory()->count(2)->create(['article_id' => $article->id]);

        $response = $this->getJson("/api/articles/{$article->id}");

        $response->assertOk()
            ->assertJsonPath('id', $article->id)
            ->assertJsonPath('comments_count', 2)
            ->assertJsonPath('author_name', $author->name)
            ->assertJsonPath('author_soft_deleted', false);
    }

    public function test_show_detects_soft_deleted_author(): void
    {
        $author = Author::factory()->create();
        $article = Article::factory()->create(['author_id' => $author->id]);
        $author->delete();

        $response = $this->getJson("/api/articles/{$article->id}");

        $response->assertOk()
            ->assertJsonPath('author_soft_deleted', true);
    }

    public function test_update_modifies_article(): void
    {
        $article = Article::factory()->create(['published' => false]);

        $response = $this->putJson("/api/articles/{$article->id}", [
            'title' => 'Updated Title',
            'published' => true,
        ]);

        $response->assertOk()
            ->assertJsonPath('title', 'Updated Title')
            ->assertJsonPath('published', true);
    }

    public function test_destroy_soft_deletes_article(): void
    {
        $article = Article::factory()->create();

        $response = $this->deleteJson("/api/articles/{$article->id}");

        $response->assertNoContent();
        $this->assertSoftDeleted('articles', ['id' => $article->id], 'secondary');
    }
}
