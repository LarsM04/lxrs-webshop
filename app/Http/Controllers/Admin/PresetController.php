<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Preset;
use App\Support\PresetCatalog;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Presets beheren. Voor nu alleen het overzicht (de R van CRUD);
 * toevoegen, bewerken en verwijderen volgen in stap 3 en 4.
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
}
