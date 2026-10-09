<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Preset;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /** GET /admin — startpagina met een paar tellers. */
    public function __invoke(): View
    {
        return view('admin.dashboard', [
            'aantalPresets' => Preset::query()->count(),
            'aantalCategorieen' => Category::query()->count(),
            'aantalUitgelicht' => Preset::query()->where('is_featured', true)->count(),
        ]);
    }
}
