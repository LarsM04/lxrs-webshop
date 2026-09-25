<?php

namespace Tests\Feature\Http\Controllers\Api;

use App\Models\Category;
use App\Models\Preset;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_returns_categories_with_their_preset_count(): void
    {
        $zooms = Category::factory()->create(['name' => 'Zooms']);
        Category::factory()->create(['name' => 'Shakes']);
        Preset::factory()->count(3)->for($zooms)->create();

        $this->getJson(route('api.categories.index'))
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.name', 'Zooms')
            ->assertJsonPath('data.0.presets_count', 3)
            ->assertJsonPath('data.1.presets_count', 0);
    }
}
