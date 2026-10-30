<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\PresetFormRequest;
use App\Models\Preset;
use App\Support\PresetCatalog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Presets beheren: het overzicht (de R van CRUD), toevoegen (C),
 * bewerken (U) en verwijderen (D).
 */
class PresetController extends Controller
{
    public const PER_PAGINA = 25;

    /**
     * GET /admin/presets — alle presets in een tabel, met dezelfde filters
     * als de website: ?zoek=glow, ?categorie=zooms en ?soort=pack.
     */
    public function index(Request $request): View
    {
        $categorieSlug = $request->query('categorie');
        $zoekterm = trim((string) $request->query('zoek', ''));
        $soort = $request->query('soort');

        // Alleen soorten die we kennen; de rest negeren we stilzwijgend.
        if (! in_array($soort, Preset::SOORTEN, true)) {
            $soort = null;
        }

        $presets = PresetCatalog::filterQuery($categorieSlug, $zoekterm ?: null, $soort)
            ->paginate(self::PER_PAGINA)
            ->withQueryString();

        return view('admin.presets.index', [
            'presets' => $presets,
            'categorieen' => PresetCatalog::categories(),
            'soorten' => PresetCatalog::soorten(),
            'actieveCategorie' => PresetCatalog::findCategory($categorieSlug),
            'actieveSoort' => $soort,
            'zoekterm' => $zoekterm,
        ]);
    }

    /** GET /admin/presets/create — leeg formulier voor een nieuwe preset. */
    public function create(): View
    {
        return view('admin.presets.create', [
            'preset' => new Preset,
            'categorieen' => PresetCatalog::categories(),
            'soorten' => PresetCatalog::soorten(),
        ]);
    }

    /** POST /admin/presets */
    public function store(PresetFormRequest $request): RedirectResponse
    {
        Preset::create($request->validated());

        return to_route('admin.presets.index')->with('status', 'Preset opgeslagen');
    }

    /** GET /admin/presets/{preset}/edit — hetzelfde formulier, ingevuld. */
    public function edit(Preset $preset): View
    {
        return view('admin.presets.edit', [
            'preset' => $preset,
            'categorieen' => PresetCatalog::categories(),
            'soorten' => PresetCatalog::soorten(),
        ]);
    }

    /** PUT /admin/presets/{preset} */
    public function update(PresetFormRequest $request, Preset $preset): RedirectResponse
    {
        $preset->update($request->validated());

        return to_route('admin.presets.index')->with('status', 'Preset opgeslagen');
    }

    /**
     * DELETE /admin/presets/{preset}
     *
     * Alleen via een formulier met DELETE, nooit via een gewone link: een link
     * kan door een browser of bot geopend worden zonder dat iemand klikt.
     */
    public function destroy(Preset $preset): RedirectResponse
    {
        $preset->delete();

        return to_route('admin.presets.index')->with('status', 'Preset verwijderd');
    }
}
