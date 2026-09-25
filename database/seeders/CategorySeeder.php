<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * De categorieen van het LXRS Edit Pack.
     *
     * Deze lijst moet gelijk lopen met CATEGORIEEN bovenin
     * tools/catalogus-genereren.mjs -- de ids worden in database/data/presets.php
     * als category_id gebruikt.
     */
    public function run(): void
    {
        $categorieen = [
            ['id' => 1, 'slug' => 'color-corrections', 'name' => 'Color Corrections', 'afkorting' => "CC's",
                'omschrijving' => 'Kleurgrades die je hele edit in een klap laten kloppen.'],
            ['id' => 2, 'slug' => 'text-presets', 'name' => 'Text Presets', 'afkorting' => 'Text',
                'omschrijving' => 'Titels, glows en fades die op de beat binnenkomen.'],
            ['id' => 3, 'slug' => 'zooms', 'name' => 'Zooms', 'afkorting' => 'Zooms',
                'omschrijving' => 'Smooth zooms en punch-ins, klaar om te slepen.'],
            ['id' => 4, 'slug' => 'shakes', 'name' => 'Shakes', 'afkorting' => 'Shakes',
                'omschrijving' => 'Camera shakes met gewicht, van subtiel tot een dreun.'],
            ['id' => 5, 'slug' => 'effects', 'name' => 'Effects', 'afkorting' => 'FX',
                'omschrijving' => 'Halftone, panning, motion blur en transities.'],
            ['id' => 6, 'slug' => 'twixtor', 'name' => 'Twixtor', 'afkorting' => 'Twixtor',
                'omschrijving' => 'Mijn twixtor-instellingen voor slow motion zonder artefacten.'],
            ['id' => 7, 'slug' => 'audio', 'name' => 'Audio', 'afkorting' => 'Audio',
                'omschrijving' => 'Audio spectrum, wiggle en fades voor je intro en outro.'],
            ['id' => 8, 'slug' => 'bundels', 'name' => 'Bundels', 'afkorting' => 'Bundel',
                'omschrijving' => 'Alles bij elkaar, voor de beste prijs per preset.'],
        ];

        foreach ($categorieen as $categorie) {
            // forceCreate, omdat id niet fillable is en presets.php op deze ids leunt.
            Category::forceCreate($categorie);
        }
    }
}
