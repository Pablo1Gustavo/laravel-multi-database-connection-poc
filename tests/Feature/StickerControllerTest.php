<?php

namespace Tests\Feature;

use App\Models\Author;
use App\Models\Sticker;
use Tests\TestCase;

class StickerControllerTest extends TestCase
{
    public function test_index_returns_stickers(): void
    {
        Sticker::factory()->count(3)->create();

        $response = $this->getJson('/api/stickers');

        $response->assertOk()
            ->assertJsonCount(3);
    }

    public function test_index_filters_by_stickerable_type(): void
    {
        Sticker::factory()->count(2)->create([
            'stickerable_type' => Author::class,
            'stickerable_id' => Author::factory(),
        ]);

        $response = $this->getJson('/api/stickers?stickerable_type='.urlencode(Author::class));

        $response->assertOk()
            ->assertJsonCount(2);
    }

    public function test_index_filters_by_author_id(): void
    {
        $author = Author::factory()->create();
        Sticker::factory()->count(2)->create([
            'stickerable_type' => Author::class,
            'stickerable_id' => $author->id,
        ]);

        $otherAuthor = Author::factory()->create();
        Sticker::factory()->create([
            'stickerable_type' => Author::class,
            'stickerable_id' => $otherAuthor->id,
        ]);

        $response = $this->getJson("/api/stickers?author_id={$author->id}");

        $response->assertOk()
            ->assertJsonCount(2);
    }

    public function test_store_creates_sticker(): void
    {
        $author = Author::factory()->create();

        $response = $this->postJson('/api/stickers', [
            'name' => 'Cool Sticker',
            'stickerable_type' => Author::class,
            'stickerable_id' => $author->id,
        ]);

        $response->assertCreated()
            ->assertJsonPath('name', 'Cool Sticker');

        $this->assertDatabaseHas('stickers', ['name' => 'Cool Sticker'], 'secondary');
    }

    public function test_store_validates_required_fields(): void
    {
        $response = $this->postJson('/api/stickers', []);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['name', 'stickerable_type', 'stickerable_id']);
    }

    public function test_show_returns_sticker(): void
    {
        $author = Author::factory()->create();
        $sticker = Sticker::factory()->create([
            'stickerable_type' => Author::class,
            'stickerable_id' => $author->id,
        ]);

        $response = $this->getJson("/api/stickers/{$sticker->id}");

        $response->assertOk()
            ->assertJsonPath('id', $sticker->id)
            ->assertJsonPath('stickerable_id', $author->id);
    }

    public function test_update_modifies_sticker(): void
    {
        $sticker = Sticker::factory()->create();

        $response = $this->putJson("/api/stickers/{$sticker->id}", [
            'name' => 'Updated Sticker',
        ]);

        $response->assertOk()
            ->assertJsonPath('name', 'Updated Sticker');
    }

    public function test_destroy_deletes_sticker(): void
    {
        $sticker = Sticker::factory()->create();

        $response = $this->deleteJson("/api/stickers/{$sticker->id}");

        $response->assertNoContent();
        $this->assertDatabaseMissing('stickers', ['id' => $sticker->id], 'secondary');
    }
}
