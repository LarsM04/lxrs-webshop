<?php

namespace App\Http\Controllers;

use App\Support\PresetCatalog;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class PresetController extends Controller
{
    /** Overzicht met filter op categorie en een zoekterm. */
    public function index(Request $request): View
    {
        $categorieSlug = $request->query('categorie');
        $zoekterm = trim((string) $request->query('zoek', ''));

        return view('presets.index', [
            'presets' => PresetCatalog::filter($categorieSlug, $zoekterm ?: null),
            'categorieen' => PresetCatalog::categories(),
            'actieveCategorie' => PresetCatalog::findCategory($categorieSlug),
            'zoekterm' => $zoekterm,
        ]);
    }

    /** Detailpagina van één preset. */
    public function show(string $slug): View
    {
        $preset = PresetCatalog::findBySlug($slug);

        if (! $preset) {
            throw new NotFoundHttpException("Preset '{$slug}' bestaat niet.");
        }

        return view('presets.show', [
            'preset' => $preset,
            'gerelateerd' => PresetCatalog::related($preset),
        ]);
    }
}
