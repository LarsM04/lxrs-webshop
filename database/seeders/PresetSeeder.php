<?php

namespace Database\Seeders;

use App\Models\Preset;
use Illuminate\Database\Seeder;

class PresetSeeder extends Seeder
{
    /**
     * De 63 producten uit het echte edit pack.
     *
     * De data staat in database/data/presets.php en wordt gegenereerd met
     * tools/catalogus-genereren.mjs. Draai CategorySeeder eerst, want elke
     * preset verwijst via category_id naar een categorie.
     */
    public function run(): void
    {
        $presets = require database_path('data/presets.php');

        foreach ($presets as $preset) {
            Preset::create($preset);
        }
    }
}
