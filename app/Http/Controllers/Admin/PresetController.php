<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\PresetFormRequest;
use App\Models\Preset;
use App\Support\PresetCatalog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
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
        $this->opslaan($request, new Preset);

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
        $this->opslaan($request, $preset);

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
        $preset->delete(); // de afbeelding ruimt het model zelf op, zie Preset::booted()

        return to_route('admin.presets.index')->with('status', 'Preset verwijderd');
    }

    /**
     * Slaat de velden op, en de afbeelding als die er is.
     *
     * Een nieuwe afbeelding komt in storage/app/public/presets/ met een
     * willekeurige naam, zodat twee uploads met dezelfde bestandsnaam elkaar
     * niet overschrijven. Wordt een afbeelding vervangen of weggehaald, dan
     * gaat het oude bestand pas weg als de preset goed is opgeslagen.
     */
    private function opslaan(PresetFormRequest $request, Preset $preset): void
    {
        $preset->fill($request->safe()->except(['afbeelding', 'afbeelding_weghalen']));

        $oudeAfbeelding = $preset->image_path;

        if ($request->hasFile('afbeelding')) {
            $preset->image_path = $request->file('afbeelding')->store('presets', 'public');
        } elseif ($request->boolean('afbeelding_weghalen')) {
            $preset->image_path = null;
        }

        $preset->save();

        if ($oudeAfbeelding && $oudeAfbeelding !== $preset->image_path) {
            Storage::disk('public')->delete($oudeAfbeelding);
        }
    }
}
