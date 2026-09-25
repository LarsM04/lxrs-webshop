<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\Category;
use App\Models\Preset;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HomeControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_homepage_shows_only_featured_presets(): void
    {
        Preset::factory()->pack()->featured()->create(['name' => 'Zooms Pack']);
        Preset::factory()->create(['name' => 'Smooth Zoom In']);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Zooms Pack')
            ->assertDontSee('Smooth Zoom In');
    }

    public function test_homepage_shows_the_number_of_presets_per_category(): void
    {
        $category = Category::factory()->create(['name' => 'Shakes']);
        Preset::factory()->count(3)->for($category)->create();

        $this->get(route('home'))
            ->assertSeeInOrder(['Shakes', '3 presets']);
    }
}
