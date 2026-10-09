<?php

namespace Tests\Feature\Http\Controllers\Admin;

use App\Models\Category;
use App\Models\Preset;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_the_login_page(): void
    {
        $this->get(route('admin.dashboard'))
            ->assertRedirect(route('admin.login'));
    }

    public function test_shows_the_number_of_presets_categories_and_featured_presets(): void
    {
        $category = Category::factory()->create();
        Category::factory()->create();
        Preset::factory()->count(3)->for($category)->create();
        Preset::factory()->featured()->for($category)->create();

        $this->actingAs(User::factory()->create())
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSeeInOrder([
                'Presets in de shop', '4',
                'Categorieën', '2',
                'Uitgelicht op de homepage', '1',
            ]);
    }
}
