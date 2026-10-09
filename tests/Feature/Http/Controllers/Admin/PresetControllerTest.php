<?php

namespace Tests\Feature\Http\Controllers\Admin;

use App\Http\Controllers\Admin\PresetController;
use App\Models\Category;
use App\Models\Preset;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * De filters zelf (zoeken, categorie, soort, volgorde) zijn al getest via de
 * website in Tests\Feature\Http\Controllers\PresetControllerTest. Hier staat
 * wat de beheeromgeving er zelf aan toevoegt.
 */
class PresetControllerTest extends TestCase
{
    use RefreshDatabase;

    private function alsBeheerder(): static
    {
        return $this->actingAs(User::factory()->create());
    }

    public function test_guest_is_redirected_to_the_login_page(): void
    {
        $this->get(route('admin.presets.index'))
            ->assertRedirect(route('admin.login'));
    }

    public function test_lists_each_preset_with_its_category_type_price_and_featured_status(): void
    {
        $shakes = Category::factory()->create(['name' => 'Shakes']);
        Preset::factory()->pack()->featured()->for($shakes)->create(['name' => 'Shakes Pack', 'price' => 10]);

        $this->alsBeheerder()
            ->get(route('admin.presets.index'))
            ->assertOk()
            ->assertSeeInOrder(['Shakes Pack', 'Shakes', 'Pack', '8 presets', '10,00', 'Ja']);
    }

    public function test_applies_search_category_and_type_filters_together(): void
    {
        $zooms = Category::factory()->create(['slug' => 'zooms']);
        Preset::factory()->pack()->for($zooms)->create(['name' => 'Glow Zooms Pack']);
        Preset::factory()->for($zooms)->create(['name' => 'Glow Zoom In']);
        Preset::factory()->pack()->create(['name' => 'Glow Text Pack']);
        Preset::factory()->pack()->for($zooms)->create(['name' => 'Smooth Zooms Pack']);

        $this->alsBeheerder()
            ->get(route('admin.presets.index', ['zoek' => 'glow', 'categorie' => 'zooms', 'soort' => 'pack']))
            ->assertSee('Glow Zooms Pack')
            ->assertDontSee('Glow Zoom In')
            ->assertDontSee('Glow Text Pack')
            ->assertDontSee('Smooth Zooms Pack');
    }

    public function test_unknown_type_filter_is_ignored(): void
    {
        Preset::factory()->create(['name' => 'Blood Text']);

        $this->alsBeheerder()
            ->get(route('admin.presets.index', ['soort' => 'onzin']))
            ->assertSee('Blood Text');
    }

    public function test_shows_25_presets_per_page(): void
    {
        Preset::factory()->count(PresetController::PER_PAGINA + 5)->create();

        $this->alsBeheerder()
            ->get(route('admin.presets.index'))
            ->assertSee('Pagina 1 van 2')
            ->assertViewHas('presets', fn (LengthAwarePaginator $presets) => $presets->count() === PresetController::PER_PAGINA);

        $this->get(route('admin.presets.index', ['page' => 2]))
            ->assertSee('Pagina 2 van 2')
            ->assertViewHas('presets', fn (LengthAwarePaginator $presets) => $presets->count() === 5);
    }

    public function test_pagination_links_keep_the_active_filters(): void
    {
        Preset::factory()->count(PresetController::PER_PAGINA + 1)->create();

        $this->alsBeheerder()
            ->get(route('admin.presets.index', ['soort' => 'los']))
            ->assertSee(route('admin.presets.index').'?soort=los&page=2');
    }

    public function test_shows_an_empty_state_when_nothing_matches(): void
    {
        Preset::factory()->create(['name' => 'Blood Text']);

        $this->alsBeheerder()
            ->get(route('admin.presets.index', ['zoek' => 'bestaat-niet']))
            ->assertSee('Niets gevonden')
            ->assertDontSee('Blood Text');
    }

    public function test_loads_the_categories_in_one_query_instead_of_one_per_row(): void
    {
        Preset::factory()->count(10)->create();
        $beheerder = User::factory()->create();

        DB::enableQueryLog();
        $this->actingAs($beheerder)->get(route('admin.presets.index'))->assertOk();

        // Telling, presets, hun categorieën in één keer, en de keuzelijst.
        // Zonder with('category') kwamen daar tien queries bij.
        $this->assertLessThanOrEqual(5, count(DB::getQueryLog()));
    }

    public function test_escapes_html_in_preset_names(): void
    {
        Preset::factory()->create(['name' => "<script>alert('xss')</script>", 'slug' => 'xss']);

        $this->alsBeheerder()
            ->get(route('admin.presets.index'))
            ->assertSee('&lt;script&gt;', false)
            ->assertDontSee("<script>alert('xss')</script>", false);
    }
}
