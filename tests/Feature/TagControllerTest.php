<?php

namespace Tests\Feature;

use App\Models\Author;
use App\Models\AuthorTag;
use App\Models\Tag;
use Tests\TestCase;

class TagControllerTest extends TestCase
{
    public function test_index_returns_tags(): void
    {
        Tag::factory()->count(3)->create();

        $response = $this->getJson('/api/tags');

        $response->assertOk()
            ->assertJsonCount(3);
    }

    public function test_index_filters_tags_by_author_id(): void
    {
        $author = Author::factory()->create();
        $attachedTag = Tag::factory()->create(['name' => 'attached']);
        AuthorTag::factory()->create(['author_id' => $author->id, 'tag_id' => $attachedTag->id]);

        Tag::factory()->create(['name' => 'unattached']);

        $response = $this->getJson("/api/tags?author_id={$author->id}");

        $response->assertOk()
            ->assertJsonCount(1)
            ->assertJsonPath('0.name', 'attached');
    }

    public function test_store_creates_tag(): void
    {
        $response = $this->postJson('/api/tags', [
            'name' => 'laravel',
        ]);

        $response->assertCreated()
            ->assertJsonPath('name', 'laravel');

        $this->assertDatabaseHas('tags', ['name' => 'laravel'], 'secondary');
    }

    public function test_store_validates_required_name(): void
    {
        $response = $this->postJson('/api/tags', []);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['name']);
    }

    public function test_store_validates_unique_name(): void
    {
        Tag::factory()->create(['name' => 'php']);

        $response = $this->postJson('/api/tags', ['name' => 'php']);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['name']);
    }

    public function test_show_returns_tag(): void
    {
        $tag = Tag::factory()->create();

        $response = $this->getJson("/api/tags/{$tag->id}");

        $response->assertOk()
            ->assertJsonPath('id', $tag->id);
    }

    public function test_update_modifies_tag(): void
    {
        $tag = Tag::factory()->create();

        $response = $this->putJson("/api/tags/{$tag->id}", [
            'name' => 'updated-tag',
        ]);

        $response->assertOk()
            ->assertJsonPath('name', 'updated-tag');
    }

    public function test_destroy_deletes_tag(): void
    {
        $tag = Tag::factory()->create();

        $response = $this->deleteJson("/api/tags/{$tag->id}");

        $response->assertNoContent();
        $this->assertDatabaseMissing('tags', ['id' => $tag->id], 'secondary');
    }
}
