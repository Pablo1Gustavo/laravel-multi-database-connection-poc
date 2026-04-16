<?php

namespace Tests\Feature;

use App\Models\Author;
use App\Models\Profile;
use Tests\TestCase;

class ProfileControllerTest extends TestCase
{
    public function test_index_returns_profiles(): void
    {
        Profile::factory()->count(3)->create();

        $response = $this->getJson('/api/profiles');

        $response->assertOk()
            ->assertJsonCount(3);
    }

    public function test_index_filters_by_author_name_via_join(): void
    {
        $author = Author::factory()->create(['name' => 'John Doe']);
        Profile::factory()->create(['author_id' => $author->id]);

        $other = Author::factory()->create(['name' => 'Jane Smith']);
        Profile::factory()->create(['author_id' => $other->id]);

        $response = $this->getJson('/api/profiles?author_name=John');

        $response->assertOk()
            ->assertJsonCount(1);
    }

    public function test_index_filters_profiles_whose_author_exists(): void
    {
        Profile::factory()->count(2)->create();

        $response = $this->getJson('/api/profiles?author_has_articles=1');

        $response->assertOk();
    }

    public function test_index_sorts_by_author_name(): void
    {
        $authorA = Author::factory()->create(['name' => 'Alice']);
        $authorZ = Author::factory()->create(['name' => 'Zack']);

        $profileZ = Profile::factory()->create(['author_id' => $authorZ->id]);
        $profileA = Profile::factory()->create(['author_id' => $authorA->id]);

        $response = $this->getJson('/api/profiles?sort=author_name');

        $response->assertOk()
            ->assertJsonPath('0.id', $profileA->id);
    }

    public function test_store_creates_profile(): void
    {
        $author = Author::factory()->create();

        $response = $this->postJson('/api/profiles', [
            'author_id' => $author->id,
            'website' => 'https://example.com',
            'twitter_handle' => 'johndoe',
        ]);

        $response->assertCreated()
            ->assertJsonPath('author_id', $author->id);

        $this->assertDatabaseHas('profiles', ['author_id' => $author->id], 'secondary');
    }

    public function test_store_validates_required_fields(): void
    {
        $response = $this->postJson('/api/profiles', []);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['author_id']);
    }

    public function test_store_validates_unique_author_id(): void
    {
        $author = Author::factory()->create();
        Profile::factory()->create(['author_id' => $author->id]);

        $response = $this->postJson('/api/profiles', [
            'author_id' => $author->id,
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['author_id']);
    }

    public function test_index_sorts_by_author_email(): void
    {
        $authorA = Author::factory()->create(['email' => 'alice@example.com']);
        $authorZ = Author::factory()->create(['email' => 'zack@example.com']);

        $profileZ = Profile::factory()->create(['author_id' => $authorZ->id]);
        $profileA = Profile::factory()->create(['author_id' => $authorA->id]);

        $response = $this->getJson('/api/profiles?sort=author_email');

        $response->assertOk()
            ->assertJsonPath('0.id', $profileA->id);
    }

    public function test_show_returns_profile_with_author_via_join(): void
    {
        $author = Author::factory()->create();
        $profile = Profile::factory()->create(['author_id' => $author->id]);

        $response = $this->getJson("/api/profiles/{$profile->id}");

        $response->assertOk()
            ->assertJsonPath('id', $profile->id)
            ->assertJsonPath('author_id', $author->id)
            ->assertJsonPath('author_name', $author->name)
            ->assertJsonPath('author_email', $author->email)
            ->assertJsonPath('author_soft_deleted', false);
    }

    public function test_show_detects_soft_deleted_author_via_join(): void
    {
        $author = Author::factory()->create();
        $profile = Profile::factory()->create(['author_id' => $author->id]);
        $author->delete();

        $response = $this->getJson("/api/profiles/{$profile->id}");

        $response->assertOk()
            ->assertJsonPath('author_name', $author->name)
            ->assertJsonPath('author_soft_deleted', true);
    }

    public function test_update_modifies_profile(): void
    {
        $profile = Profile::factory()->create();

        $response = $this->putJson("/api/profiles/{$profile->id}", [
            'website' => 'https://updated.com',
        ]);

        $response->assertOk()
            ->assertJsonPath('website', 'https://updated.com');
    }

    public function test_destroy_deletes_profile(): void
    {
        $profile = Profile::factory()->create();

        $response = $this->deleteJson("/api/profiles/{$profile->id}");

        $response->assertNoContent();
        $this->assertDatabaseMissing('profiles', ['id' => $profile->id], 'secondary');
    }
}
