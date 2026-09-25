<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\Category;
use App\Models\Preset;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PresetControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_overview_renders_presets_from_the_database(): void
    {
        Preset::factory()->create(['name' => 'Midnight CC']);
        Preset::factory()->create(['name' => 'Lava Text']);

        $this->get(route('presets.index'))
            ->assertOk()
            ->assertSee('Midnight CC')
            ->assertSee('Lava Text');
    }

    public function test_overview_lists_bundle_first_then_packs_then_single_presets(): void
    {
        Preset::factory()->create(['name' => 'Losse Zoom']);
        Preset::factory()->pack()->create(['name' => 'Zooms Pack']);
        Preset::factory()->bundel()->create(['name' => 'Complete Pack']);

        $this->get(route('presets.index'))
            ->assertSeeInOrder(['Complete Pack', 'Zooms Pack', 'Losse Zoom']);
    }

    public function test_category_filter_only_shows_presets_from_that_category(): void
    {
        $zooms = Category::factory()->create(['slug' => 'zooms']);
        Preset::factory()->for($zooms)->create(['name' => 'Smooth Zoom In']);
        Preset::factory()->create(['name' => 'Heavy Shake']);

        $this->get(route('presets.index', ['categorie' => 'zooms']))
            ->assertSee('Smooth Zoom In')
            ->assertDontSee('Heavy Shake');
    }

    public function test_type_filter_only_shows_that_type(): void
    {
        Preset::factory()->pack()->create(['name' => 'Text Presets Pack']);
        Preset::factory()->create(['name' => 'Blood Text']);

        $this->get(route('presets.index', ['soort' => 'pack']))
            ->assertSee('Text Presets Pack')
            ->assertDontSee('Blood Text');
    }

    public function test_unknown_type_filter_is_ignored(): void
    {
        Preset::factory()->create(['name' => 'Blood Text']);

        $this->get(route('presets.index', ['soort' => 'onzin']))
            ->assertOk()
            ->assertSee('Blood Text');
    }

    public function test_search_matches_name_tagline_and_category_name_case_insensitively(): void
    {
        $shakes = Category::factory()->create(['name' => 'Shakes']);
        Preset::factory()->create(['name' => 'Glow Text']);
        Preset::factory()->create(['name' => 'Cold CC', 'tagline' => 'Een koele GLOW over alles']);
        Preset::factory()->for($shakes)->create(['name' => 'Impact Hit']);
        Preset::factory()->create(['name' => 'Smooth Zoom In']);

        $this->get(route('presets.index', ['zoek' => 'glow']))
            ->assertSee('Glow Text')
            ->assertSee('Cold CC')
            ->assertDontSee('Smooth Zoom In');

        $this->get(route('presets.index', ['zoek' => 'shakes']))
            ->assertSee('Impact Hit')
            ->assertDontSee('Smooth Zoom In');
    }

    public function test_search_does_not_escape_the_category_filter(): void
    {
        $zooms = Category::factory()->create(['slug' => 'zooms']);
        Preset::factory()->for($zooms)->create(['name' => 'Glow Zoom']);
        Preset::factory()->create(['name' => 'Cold CC', 'tagline' => 'Met een glow']);

        $this->get(route('presets.index', ['categorie' => 'zooms', 'zoek' => 'glow']))
            ->assertSee('Glow Zoom')
            ->assertDontSee('Cold CC');
    }

    public function test_detail_page_renders_the_preset_with_its_category_and_price(): void
    {
        $category = Category::factory()->create(['name' => 'Color Corrections']);
        Preset::factory()->for($category)->create([
            'name' => 'Midnight CC',
            'slug' => 'midnight-cc',
            'price' => 2.5,
        ]);

        $this->get(route('presets.show', 'midnight-cc'))
            ->assertOk()
            ->assertSee('Midnight CC')
            ->assertSee('Color Corrections')
            ->assertSee('&euro;2,50', false);
    }

    public function test_detail_page_returns_404_for_an_unknown_slug(): void
    {
        $this->get(route('presets.show', 'bestaat-niet'))->assertNotFound();
    }
}
