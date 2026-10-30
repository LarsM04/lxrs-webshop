<?php

namespace Tests\Feature\Http\Controllers\Admin;

use App\Models\Category;
use App\Models\Preset;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryControllerTest extends TestCase
{
    use RefreshDatabase;

    private function alsBeheerder(): static
    {
        return $this->actingAs(User::factory()->create());
    }

    /**
     * Wat het formulier verstuurt als alles goed is ingevuld.
     *
     * @param  array<string, mixed>  $anders
     * @return array<string, mixed>
     */
    private function formulier(array $anders = []): array
    {
        return [
            'name' => 'Color Grades',
            'slug' => '',
            'afkorting' => 'GRADES',
            'omschrijving' => 'Volledige looks in één klik.',
            ...$anders,
        ];
    }

    public function test_guest_cannot_see_or_change_categories(): void
    {
        $categorie = Category::factory()->create(['name' => 'Origineel']);

        $this->get(route('admin.categories.index'))->assertRedirect(route('admin.login'));
        $this->post(route('admin.categories.store'), $this->formulier())->assertRedirect(route('admin.login'));
        $this->put(route('admin.categories.update', $categorie), $this->formulier())->assertRedirect(route('admin.login'));
        $this->delete(route('admin.categories.destroy', $categorie))->assertRedirect(route('admin.login'));

        $this->assertDatabaseCount('categories', 1);
        $this->assertSame('Origineel', $categorie->fresh()->name);
    }

    public function test_lists_each_category_with_its_number_of_presets(): void
    {
        $shakes = Category::factory()->create(['name' => 'Shakes']);
        Preset::factory()->count(3)->for($shakes)->create();
        Category::factory()->create(['name' => 'Audio']);

        $this->alsBeheerder()
            ->get(route('admin.categories.index'))
            ->assertOk()
            ->assertSeeInOrder(['Shakes', '3', 'Audio', '0'])
            ->assertSee(route('admin.categories.create'))
            ->assertSee(route('admin.categories.edit', $shakes));
    }

    public function test_menu_links_to_the_categories(): void
    {
        $this->alsBeheerder()
            ->get(route('admin.dashboard'))
            ->assertSee(route('admin.categories.index'));
    }

    public function test_adding_a_category_shows_it_as_a_filter_on_the_website(): void
    {
        $this->alsBeheerder()
            ->post(route('admin.categories.store'), $this->formulier())
            ->assertRedirect(route('admin.categories.index'))
            ->assertSessionHas('status', 'Categorie opgeslagen');

        $categorie = Category::sole();
        $this->assertSame('color-grades', $categorie->slug); // gemaakt van de naam

        $this->get(route('presets.index'))
            ->assertSee(route('presets.index', ['categorie' => 'color-grades']));
    }

    public function test_invalid_input_shows_dutch_errors(): void
    {
        $this->alsBeheerder()
            ->post(route('admin.categories.store'), $this->formulier([
                'name' => '',
                'afkorting' => str_repeat('X', 21),
            ]))
            ->assertSessionHasErrors([
                'name' => 'Naam is verplicht.',
                'afkorting' => 'Afkorting mag niet langer zijn dan 20 tekens.',
            ]);

        $this->assertDatabaseCount('categories', 0);
    }

    public function test_slug_must_be_unique(): void
    {
        Category::factory()->create(['slug' => 'color-grades']);

        $this->alsBeheerder()
            ->post(route('admin.categories.store'), $this->formulier())
            ->assertSessionHasErrors('slug');
    }

    public function test_edit_page_shows_the_form_filled_in(): void
    {
        $categorie = Category::factory()->create(['name' => 'Shakes']);

        $this->alsBeheerder()
            ->get(route('admin.categories.edit', $categorie))
            ->assertOk()
            ->assertSee('value="Shakes"', false)
            ->assertSee(route('admin.categories.update', $categorie));
    }

    public function test_editing_a_category_saves_the_changes_and_may_keep_its_slug(): void
    {
        $categorie = Category::factory()->create(['slug' => 'shakes']);

        $this->alsBeheerder()
            ->put(route('admin.categories.update', $categorie), $this->formulier(['name' => 'Camera Shakes', 'slug' => 'shakes']))
            ->assertRedirect(route('admin.categories.index'))
            ->assertSessionHas('status', 'Categorie opgeslagen');

        $categorie->refresh();
        $this->assertSame('Camera Shakes', $categorie->name);
        $this->assertSame('shakes', $categorie->slug);
    }

    public function test_an_empty_category_can_be_deleted(): void
    {
        $categorie = Category::factory()->create();

        $this->alsBeheerder()
            ->delete(route('admin.categories.destroy', $categorie))
            ->assertRedirect(route('admin.categories.index'))
            ->assertSessionHas('status', 'Categorie verwijderd');

        $this->assertModelMissing($categorie);
    }

    public function test_a_category_with_presets_cannot_be_deleted_and_says_why(): void
    {
        $shakes = Category::factory()->create(['name' => 'Shakes']);
        Preset::factory()->count(8)->for($shakes)->create();

        $this->alsBeheerder()
            ->delete(route('admin.categories.destroy', $shakes))
            ->assertRedirect(route('admin.categories.index'))
            ->assertSessionHas('fout', 'Er zitten nog 8 presets in Shakes. Verplaats of verwijder die eerst.');

        $this->assertModelExists($shakes);
        $this->assertDatabaseCount('presets', 8);

        $this->followRedirects($this->delete(route('admin.categories.destroy', $shakes)))
            ->assertSee('Er zitten nog 8 presets in Shakes.');
    }

    public function test_one_preset_left_is_mentioned_in_singular(): void
    {
        $audio = Category::factory()->create(['name' => 'Audio']);
        Preset::factory()->for($audio)->create();

        $this->alsBeheerder()
            ->delete(route('admin.categories.destroy', $audio))
            ->assertSessionHas('fout', 'Er zit nog 1 preset in Audio. Verplaats of verwijder die eerst.');
    }
}
