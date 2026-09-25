<?php

namespace Tests\Feature\Http\Controllers\Api;

use App\Models\Category;
use App\Models\Preset;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PresetControllerTest extends TestCase
{
    use RefreshDatabase;

    private const SLEUTEL = 'test-sleutel';

    protected function setUp(): void
    {
        parent::setUp();

        config(['services.lxrs_api.key' => self::SLEUTEL]);
    }

    /** @return array<string, string> */
    private function metSleutel(): array
    {
        return ['X-API-Key' => self::SLEUTEL];
    }

    /** @return array<string, mixed> */
    private function geldigePreset(Category $category, array $anders = []): array
    {
        return [
            'category_id' => $category->id,
            'name' => 'Cold CC',
            'soort' => 'los',
            'price' => 2.5,
            'tagline' => 'Koele kleurgrade',
            'description' => 'Eén losse preset.',
            ...$anders,
        ];
    }

    public function test_index_returns_all_presets_with_their_category(): void
    {
        $category = Category::factory()->create(['name' => 'Zooms']);
        Preset::factory()->count(2)->for($category)->create();

        $this->getJson(route('api.presets.index'))
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.category.name', 'Zooms');
    }

    public function test_index_applies_the_same_filters_as_the_website(): void
    {
        Preset::factory()->pack()->create(['name' => 'Zooms Pack']);
        Preset::factory()->create(['name' => 'Smooth Zoom']);

        $this->getJson(route('api.presets.index', ['soort' => 'pack']))
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.name', 'Zooms Pack');
    }

    public function test_show_returns_one_preset_without_the_download_path(): void
    {
        $preset = Preset::factory()->create(['name' => 'Cold CC', 'download_path' => 'geheim/cold.ffx']);

        $this->getJson(route('api.presets.show', $preset))
            ->assertOk()
            ->assertJsonPath('data.name', 'Cold CC')
            ->assertJsonMissingPath('data.download_path');
    }

    public function test_show_returns_404_for_an_unknown_id(): void
    {
        $this->getJson(route('api.presets.show', 999))->assertNotFound();
    }

    public function test_store_creates_a_preset_and_returns_201(): void
    {
        $category = Category::factory()->create();

        $this->postJson(route('api.presets.store'), $this->geldigePreset($category), $this->metSleutel())
            ->assertCreated()
            ->assertJsonPath('data.slug', 'cold-cc')
            ->assertJsonPath('data.price', 2.5)
            ->assertJsonPath('data.aantal', 1)
            ->assertJsonPath('data.is_featured', false);

        $this->assertDatabaseHas('presets', ['slug' => 'cold-cc', 'category_id' => $category->id]);
    }

    public function test_store_rejects_invalid_input_with_422(): void
    {
        $this->postJson(route('api.presets.store'), [
            'name' => '',
            'price' => 'gratis',
            'category_id' => 999,
            'soort' => 'iets',
        ], $this->metSleutel())
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['name', 'price', 'category_id', 'soort', 'tagline', 'description']);

        $this->assertDatabaseCount('presets', 0);
    }

    public function test_store_rejects_a_slug_that_is_already_taken(): void
    {
        $category = Category::factory()->create();
        Preset::factory()->create(['slug' => 'cold-cc']);

        $this->postJson(route('api.presets.store'), $this->geldigePreset($category), $this->metSleutel())
            ->assertUnprocessable()
            ->assertJsonValidationErrors('slug');
    }

    public function test_put_replaces_the_preset(): void
    {
        $preset = Preset::factory()->create();
        $category = Category::factory()->create();

        $this->putJson(
            route('api.presets.update', $preset),
            $this->geldigePreset($category, ['slug' => $preset->slug, 'name' => 'Nieuwe naam']),
            $this->metSleutel(),
        )
            ->assertOk()
            ->assertJsonPath('data.name', 'Nieuwe naam');

        $this->assertSame($category->id, $preset->fresh()->category_id);
    }

    public function test_put_requires_all_required_fields(): void
    {
        $preset = Preset::factory()->create();

        $this->putJson(route('api.presets.update', $preset), ['price' => 3], $this->metSleutel())
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['name', 'category_id']);
    }

    public function test_patch_changes_only_the_fields_that_are_sent(): void
    {
        $preset = Preset::factory()->create(['name' => 'Cold CC', 'price' => 4]);

        $this->patchJson(route('api.presets.update', $preset), ['price' => 3], $this->metSleutel())
            ->assertOk()
            ->assertJsonPath('data.price', 3)
            ->assertJsonPath('data.name', 'Cold CC');
    }

    public function test_update_may_keep_its_own_slug(): void
    {
        $preset = Preset::factory()->create(['slug' => 'cold-cc']);

        $this->patchJson(route('api.presets.update', $preset), ['slug' => 'cold-cc'], $this->metSleutel())
            ->assertOk();
    }

    public function test_destroy_deletes_the_preset_and_returns_204(): void
    {
        $preset = Preset::factory()->create();

        $this->deleteJson(route('api.presets.destroy', $preset), [], $this->metSleutel())
            ->assertNoContent();

        $this->assertModelMissing($preset);
    }

    public function test_write_requests_without_the_api_key_are_rejected_with_401(): void
    {
        $preset = Preset::factory()->create();
        $category = Category::factory()->create();

        $this->postJson(route('api.presets.store'), $this->geldigePreset($category))->assertUnauthorized();
        $this->patchJson(route('api.presets.update', $preset), ['price' => 1])->assertUnauthorized();
        $this->deleteJson(route('api.presets.destroy', $preset))->assertUnauthorized();

        $this->assertModelExists($preset);
        $this->assertDatabaseCount('presets', 1);
    }

    public function test_write_requests_with_a_wrong_api_key_are_rejected_with_401(): void
    {
        $preset = Preset::factory()->create();

        $this->deleteJson(route('api.presets.destroy', $preset), [], ['X-API-Key' => 'fout'])
            ->assertUnauthorized();

        $this->assertModelExists($preset);
    }

    public function test_write_requests_are_rejected_when_no_api_key_is_configured(): void
    {
        config(['services.lxrs_api.key' => null]);
        $preset = Preset::factory()->create();

        $this->deleteJson(route('api.presets.destroy', $preset), [], ['X-API-Key' => ''])
            ->assertUnauthorized();

        $this->assertModelExists($preset);
    }
}
