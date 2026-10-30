<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CategoryRequest;
use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

/**
 * Categorieën beheren: volledige CRUD voor de tweede tabel.
 */
class CategoryController extends Controller
{
    /** GET /admin/categories — alle categorieën, met hoeveel presets erin zitten. */
    public function index(): View
    {
        return view('admin.categories.index', [
            'categorieen' => Category::query()->withCount('presets')->orderBy('id')->get(),
        ]);
    }

    /** GET /admin/categories/create */
    public function create(): View
    {
        return view('admin.categories.create', ['categorie' => new Category]);
    }

    /** POST /admin/categories */
    public function store(CategoryRequest $request): RedirectResponse
    {
        Category::create($request->validated());

        return to_route('admin.categories.index')->with('status', 'Categorie opgeslagen');
    }

    /** GET /admin/categories/{category}/edit */
    public function edit(Category $category): View
    {
        return view('admin.categories.edit', ['categorie' => $category]);
    }

    /** PUT /admin/categories/{category} */
    public function update(CategoryRequest $request, Category $category): RedirectResponse
    {
        $category->update($request->validated());

        return to_route('admin.categories.index')->with('status', 'Categorie opgeslagen');
    }

    /**
     * DELETE /admin/categories/{category}
     *
     * Een categorie met presets erin kan niet weg. De database houdt dat ook
     * tegen (restrictOnDelete in de migration), maar dan krijg je een
     * foutpagina. Hier controleren we het eerst, met een duidelijke melding.
     */
    public function destroy(Category $category): RedirectResponse
    {
        $aantal = $category->presets()->count();

        if ($aantal > 0) {
            $nogOver = $aantal === 1 ? 'zit nog 1 preset' : "zitten nog {$aantal} presets";

            return to_route('admin.categories.index')->with('fout',
                "Er {$nogOver} in {$category->name}. Verplaats of verwijder die eerst."
            );
        }

        $category->delete();

        return to_route('admin.categories.index')->with('status', 'Categorie verwijderd');
    }
}
