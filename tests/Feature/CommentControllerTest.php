<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\Author;
use App\Models\Comment;
use Tests\TestCase;

class CommentControllerTest extends TestCase
{
    public function test_index_returns_comments(): void
    {
        Comment::factory()->count(3)->create();

        $response = $this->getJson('/api/comments');

        $response->assertOk()
            ->assertJsonCount(3);
    }

    public function test_index_filters_by_author_name_via_nested_join(): void
    {
        $author = Author::factory()->create(['name' => 'John Doe']);
        $article = Article::factory()->create(['author_id' => $author->id]);
        Comment::factory()->count(2)->create(['article_id' => $article->id]);

        $otherAuthor = Author::factory()->create(['name' => 'Jane Smith']);
        $otherArticle = Article::factory()->create(['author_id' => $otherAuthor->id]);
        Comment::factory()->create(['article_id' => $otherArticle->id]);

        $response = $this->getJson('/api/comments?author_name=John');

        $response->assertOk()
            ->assertJsonCount(2);
    }

    public function test_index_filters_comments_on_published_articles(): void
    {
        $publishedArticle = Article::factory()->published()->create();
        Comment::factory()->count(2)->create(['article_id' => $publishedArticle->id]);

        $draftArticle = Article::factory()->create(['published' => false]);
        Comment::factory()->create(['article_id' => $draftArticle->id]);

        $response = $this->getJson('/api/comments?published_articles_only=1');

        $response->assertOk()
            ->assertJsonCount(2);
    }

    public function test_index_sorts_by_article_title(): void
    {
        $articleA = Article::factory()->create(['title' => 'Alpha Article']);
        $articleZ = Article::factory()->create(['title' => 'Zeta Article']);

        $commentOnZ = Comment::factory()->create(['article_id' => $articleZ->id]);
        $commentOnA = Comment::factory()->create(['article_id' => $articleA->id]);

        $response = $this->getJson('/api/comments?sort=article_title');

        $response->assertOk()
            ->assertJsonPath('0.id', $commentOnA->id);
    }

    public function test_store_creates_comment(): void
    {
        $article = Article::factory()->create();

        $response = $this->postJson('/api/comments', [
            'article_id' => $article->id,
            'body' => 'Great article!',
            'author_name' => 'Reader',
        ]);

        $response->assertCreated()
            ->assertJsonPath('body', 'Great article!')
            ->assertJsonPath('article_id', $article->id);

        $this->assertDatabaseHas('comments', ['body' => 'Great article!'], 'secondary');
    }

    public function test_store_validates_required_fields(): void
    {
        $response = $this->postJson('/api/comments', []);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['article_id', 'body', 'author_name']);
    }

    public function test_store_validates_article_exists_on_secondary(): void
    {
        $response = $this->postJson('/api/comments', [
            'article_id' => 999,
            'body' => 'Comment on nothing',
            'author_name' => 'Ghost',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['article_id']);
    }

    public function test_index_sorts_by_author_name_nested_cross_db(): void
    {
        $authorA = Author::factory()->create(['name' => 'Alice']);
        $authorZ = Author::factory()->create(['name' => 'Zack']);

        $articleByA = Article::factory()->create(['author_id' => $authorA->id]);
        $articleByZ = Article::factory()->create(['author_id' => $authorZ->id]);

        $commentByZ = Comment::factory()->create(['article_id' => $articleByZ->id]);
        $commentByA = Comment::factory()->create(['article_id' => $articleByA->id]);

        $response = $this->getJson('/api/comments?sort=author_name');

        $response->assertOk()
            ->assertJsonPath('0.id', $commentByA->id);
    }

    public function test_show_returns_comment_with_article_and_author_via_join(): void
    {
        $author = Author::factory()->create();
        $article = Article::factory()->create(['author_id' => $author->id]);
        $comment = Comment::factory()->create(['article_id' => $article->id]);

        $response = $this->getJson("/api/comments/{$comment->id}");

        $response->assertOk()
            ->assertJsonPath('id', $comment->id)
            ->assertJsonPath('article_id', $article->id)
            ->assertJsonPath('article_author_name', $author->name)
            ->assertJsonPath('article_author_email', $author->email)
            ->assertJsonPath('article_author_soft_deleted', false);
    }

    public function test_show_detects_soft_deleted_author_via_nested_join(): void
    {
        $author = Author::factory()->create();
        $article = Article::factory()->create(['author_id' => $author->id]);
        $comment = Comment::factory()->create(['article_id' => $article->id]);
        $author->delete();

        $response = $this->getJson("/api/comments/{$comment->id}");

        $response->assertOk()
            ->assertJsonPath('article_author_name', $author->name)
            ->assertJsonPath('article_author_soft_deleted', true);
    }

    public function test_update_modifies_comment(): void
    {
        $comment = Comment::factory()->create();

        $response = $this->putJson("/api/comments/{$comment->id}", [
            'body' => 'Updated comment body',
        ]);

        $response->assertOk()
            ->assertJsonPath('body', 'Updated comment body');
    }

    public function test_destroy_deletes_comment(): void
    {
        $comment = Comment::factory()->create();

        $response = $this->deleteJson("/api/comments/{$comment->id}");

        $response->assertNoContent();
        $this->assertDatabaseMissing('comments', ['id' => $comment->id], 'secondary');
    }
}
