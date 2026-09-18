<?php

namespace App\Http\Controllers;

use App\Support\Edits;
use App\Support\PresetCatalog;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __invoke(): View
    {
        $presets = PresetCatalog::all();

        return view('home', [
            'uitgelicht' => PresetCatalog::featured(),
            'categorieen' => PresetCatalog::categories(),
            'aantalPresets' => $presets->count(),
            'aantalPerCategorie' => $presets->groupBy('category_id')->map->count(),
            'edits' => Edits::all(),
        ]);
    }
}
