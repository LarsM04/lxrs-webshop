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

    public function test_index_links_to_the_new_preset_form_and_to_editing_each_preset(): void
    {
        $preset = Preset::factory()->create();

        $this->alsBeheerder()
            ->get(route('admin.presets.index'))
            ->assertSee(route('admin.presets.create'))
            ->assertSee(route('admin.presets.edit', $preset));
    }

    /*
     * Toevoegen (C)
     */

    /**
     * Wat het formulier verstuurt als alles goed is ingevuld. Net als in een
     * echte browser is alles tekst, en staat "wat je krijgt" op losse regels.
     *
     * @param  array<string, mixed>  $anders
     * @return array<string, mixed>
     */
    private function formulier(array $anders = []): array
    {
        return [
            'category_id' => (string) Category::factory()->create()->id,
            'name' => 'Cold CC',
            'slug' => '',
            'soort' => 'los',
            'aantal' => '1',
            'price' => '4.50',
            'tagline' => 'Koele kleuren voor nachtedits',
            'description' => 'Een blauwe color correction.',
            'includes' => "1 preset als .ffx\r\n\r\n  Uitleg als pdf  \r\n",
            'bestandsgrootte' => '120 KB',
            'ae_version' => 'After Effects 2020 of nieuwer',
            'is_featured' => '1',
            ...$anders,
        ];
    }

    public function test_guest_cannot_open_the_form_or_add_a_preset(): void
    {
        $this->get(route('admin.presets.create'))->assertRedirect(route('admin.login'));
        $this->post(route('admin.presets.store'), $this->formulier())->assertRedirect(route('admin.login'));

        $this->assertDatabaseCount('presets', 0);
    }

    public function test_create_page_shows_an_empty_form_with_the_categories(): void
    {
        Category::factory()->create(['name' => 'Shakes']);

        $this->alsBeheerder()
            ->get(route('admin.presets.create'))
            ->assertOk()
            ->assertSee('Nieuwe preset')
            ->assertSee('Shakes')
            ->assertSee(route('admin.presets.store'));
    }

    public function test_adding_a_preset_saves_it_and_shows_it_on_the_website(): void
    {
        $this->alsBeheerder()
            ->post(route('admin.presets.store'), $this->formulier())
            ->assertRedirect(route('admin.presets.index'))
            ->assertSessionHas('status', 'Preset opgeslagen');

        $preset = Preset::sole();
        $this->assertSame('Cold CC', $preset->name);
        $this->assertSame('4.50', $preset->price);
        $this->assertTrue($preset->is_featured);

        $this->get(route('presets.index'))->assertSee('Cold CC');
    }

    public function test_empty_slug_is_made_from_the_name(): void
    {
        $this->alsBeheerder()->post(route('admin.presets.store'), $this->formulier(['name' => 'Midnight Glow CC']));

        $this->assertSame('midnight-glow-cc', Preset::sole()->slug);
    }

    public function test_what_you_get_becomes_a_list_with_one_item_per_line(): void
    {
        $this->alsBeheerder()->post(route('admin.presets.store'), $this->formulier());

        $this->assertSame(['1 preset als .ffx', 'Uitleg als pdf'], Preset::sole()->includes);
    }

    public function test_unchecked_featured_box_saves_as_not_featured(): void
    {
        $formulier = $this->formulier();
        unset($formulier['is_featured']); // een browser stuurt een leeg vinkje niet mee

        $this->alsBeheerder()->post(route('admin.presets.store'), $formulier);

        $this->assertFalse(Preset::sole()->is_featured);
    }

    public function test_invalid_input_shows_dutch_errors_and_keeps_what_was_filled_in(): void
    {
        $this->alsBeheerder()
            ->from(route('admin.presets.create'))
            ->post(route('admin.presets.store'), $this->formulier(['name' => '', 'price' => 'abc']))
            ->assertRedirect(route('admin.presets.create'))
            ->assertSessionHasErrors([
                'name' => 'Naam is verplicht.',
                'price' => 'Prijs moet een getal zijn.',
            ])
            ->assertSessionHasInput('tagline', 'Koele kleuren voor nachtedits');

        $this->assertDatabaseCount('presets', 0);
    }

    public function test_slug_must_be_unique(): void
    {
        Preset::factory()->create(['slug' => 'cold-cc']);

        $this->alsBeheerder()
            ->post(route('admin.presets.store'), $this->formulier())
            ->assertSessionHasErrors('slug');

        $this->assertDatabaseCount('presets', 1);
    }

    /*
     * Bewerken (U)
     */

    public function test_guest_cannot_edit_a_preset(): void
    {
        $preset = Preset::factory()->create(['name' => 'Origineel']);

        $this->get(route('admin.presets.edit', $preset))->assertRedirect(route('admin.login'));
        $this->put(route('admin.presets.update', $preset), $this->formulier())->assertRedirect(route('admin.login'));

        $this->assertSame('Origineel', $preset->fresh()->name);
    }

    public function test_edit_page_shows_the_form_filled_in(): void
    {
        $preset = Preset::factory()->pack()->create([
            'name' => 'Shakes Pack',
            'includes' => ['Shake 1', 'Shake 2'],
        ]);

        $this->alsBeheerder()
            ->get(route('admin.presets.edit', $preset))
            ->assertOk()
            ->assertSee('value="Shakes Pack"', false)
            ->assertSee("Shake 1\nShake 2")
            ->assertSee(route('admin.presets.update', $preset));
    }

    public function test_editing_a_preset_saves_the_changes(): void
    {
        $preset = Preset::factory()->featured()->create(['slug' => 'cold-cc']);

        $formulier = $this->formulier(['name' => 'Cold CC v2', 'slug' => 'cold-cc', 'price' => '6']);
        unset($formulier['is_featured']);

        $this->alsBeheerder()
            ->put(route('admin.presets.update', $preset), $formulier)
            ->assertRedirect(route('admin.presets.index'))
            ->assertSessionHas('status', 'Preset opgeslagen');

        $preset->refresh();
        $this->assertSame('Cold CC v2', $preset->name);
        $this->assertSame('cold-cc', $preset->slug); // eigen slug houden mag
        $this->assertSame('6.00', $preset->price);
        $this->assertFalse($preset->is_featured);
    }

    public function test_editing_cannot_take_the_slug_of_another_preset(): void
    {
        Preset::factory()->create(['slug' => 'bezet']);
        $preset = Preset::factory()->create(['slug' => 'eigen']);

        $this->alsBeheerder()
            ->put(route('admin.presets.update', $preset), $this->formulier(['slug' => 'bezet']))
            ->assertSessionHasErrors('slug');

        $this->assertSame('eigen', $preset->fresh()->slug);
    }
}
