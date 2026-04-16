<?php

namespace Tests\Feature;

use App\Models\Label;
use Tests\TestCase;

class LabelControllerTest extends TestCase
{
    public function test_index_returns_labels(): void
    {
        Label::factory()->count(3)->create();

        $response = $this->getJson('/api/labels');

        $response->assertOk()
            ->assertJsonCount(3);
    }

    public function test_store_creates_label(): void
    {
        $response = $this->postJson('/api/labels', [
            'name' => 'bug',
            'color' => '#ff0000',
        ]);

        $response->assertCreated()
            ->assertJsonPath('name', 'bug')
            ->assertJsonPath('color', '#ff0000');

        $this->assertDatabaseHas('labels', ['name' => 'bug'], 'secondary');
    }

    public function test_store_validates_required_name(): void
    {
        $response = $this->postJson('/api/labels', []);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['name']);
    }

    public function test_store_uses_default_color(): void
    {
        $response = $this->postJson('/api/labels', [
            'name' => 'feature',
        ]);

        $response->assertCreated();
        $this->assertDatabaseHas('labels', ['name' => 'feature', 'color' => '#000000'], 'secondary');
    }

    public function test_show_returns_label(): void
    {
        $label = Label::factory()->create();

        $response = $this->getJson("/api/labels/{$label->id}");

        $response->assertOk()
            ->assertJsonPath('id', $label->id);
    }

    public function test_update_modifies_label(): void
    {
        $label = Label::factory()->create();

        $response = $this->putJson("/api/labels/{$label->id}", [
            'name' => 'updated',
            'color' => '#00ff00',
        ]);

        $response->assertOk()
            ->assertJsonPath('name', 'updated')
            ->assertJsonPath('color', '#00ff00');
    }

    public function test_destroy_deletes_label(): void
    {
        $label = Label::factory()->create();

        $response = $this->deleteJson("/api/labels/{$label->id}");

        $response->assertNoContent();
        $this->assertDatabaseMissing('labels', ['id' => $label->id], 'secondary');
    }
}
