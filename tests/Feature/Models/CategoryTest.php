<?php

namespace Tests\Feature\Models;

use App\Models\Category;
use App\Models\Preset;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_category_with_presets_cannot_be_deleted(): void
    {
        $category = Category::factory()->has(Preset::factory())->create();

        $this->expectException(QueryException::class);

        $category->delete();
    }

    public function test_empty_category_can_be_deleted(): void
    {
        $category = Category::factory()->create();

        $category->delete();

        $this->assertModelMissing($category);
    }
}
