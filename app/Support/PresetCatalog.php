<?php

namespace App\Support;

use Illuminate\Support\Collection;

/**
 * De catalogus van het LXRS Edit Pack.
 *
 * De producten komen uit preset-data.php, dat gegenereerd wordt uit de
 * mappenstructuur van het echte pack:
 *
 *     node tools/catalogus-genereren.mjs "<pad naar het uitgepakte pack>"
 *
 * Er zijn drie soorten producten:
 *   los     - een enkele .ffx-preset
 *   pack    - alle presets uit een categorie
 *   bundel  - het complete pack
 *
 * In fase 1 wordt de binnenkant van deze klasse Eloquent. De buitenkant blijft
 * hetzelfde, dus de Blade-views hoeven niet mee te veranderen.
 */
class PresetCatalog
{
    /**
     * De categorieen. Deze lijst moet gelijk lopen met CATEGORIEEN bovenin
     * tools/catalogus-genereren.mjs -- pas je daar iets aan, doe het hier ook.
     */
    public static function categories(): Collection
    {
        return collect([
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
        ])->map(fn ($c) => (object) $c);
    }

    /** De drie soorten, voor het filter op de overzichtspagina. */
    public static function soorten(): Collection
    {
        return collect([
            ['slug' => 'los', 'naam' => 'Losse presets'],
            ['slug' => 'pack', 'naam' => 'Packs'],
            ['slug' => 'bundel', 'naam' => 'Complete pack'],
        ])->map(fn ($s) => (object) $s);
    }

    public static function findCategory(?string $slug): ?object
    {
        return $slug ? static::categories()->firstWhere('slug', $slug) : null;
    }

    /** Alle producten, met hun categorie eraan gekoppeld. */
    public static function all(): Collection
    {
        static $alles = null;

        if ($alles === null) {
            $categories = static::categories()->keyBy('id');

            $alles = collect(require __DIR__ . '/preset-data.php')
                ->map(function ($p) use ($categories) {
                    $p['category'] = $categories->get($p['category_id']);

                    return (object) $p;
                });
        }

        return $alles;
    }

    /** Wat er op de homepage uitgelicht staat: de bundel en een paar packs. */
    public static function featured(): Collection
    {
        return static::all()
            ->where('is_featured', true)
            ->sortBy(fn ($p) => $p->soort === 'bundel' ? 0 : 1)
            ->values();
    }

    public static function findBySlug(string $slug): ?object
    {
        return static::all()->firstWhere('slug', $slug);
    }

    /**
     * Wat er bij een product past.
     *
     * Bij een los product: andere losse presets uit dezelfde categorie.
     * Bij een pack of bundel: wat erin zit, oftewel de losse presets zelf.
     */
    public static function related(object $preset, int $limit = 4): Collection
    {
        $zelfdeCategorie = static::all()
            ->where('category_id', $preset->category_id)
            ->where('id', '!=', $preset->id);

        if ($preset->soort === 'los') {
            // Het pack van deze categorie eerst: dat is de logische upsell.
            $pack = $zelfdeCategorie->firstWhere('soort', 'pack');

            return collect([$pack])
                ->filter()
                ->concat($zelfdeCategorie->where('soort', 'los')->shuffle())
                ->take($limit)
                ->values();
        }

        if ($preset->soort === 'pack') {
            return $zelfdeCategorie->where('soort', 'los')->take($limit)->values();
        }

        // De bundel: laat de packs zien die erin zitten.
        return static::all()->where('soort', 'pack')->take($limit)->values();
    }

    /**
     * Filteren op categorie, soort en zoekterm.
     *
     * Dit wordt in fase 1 een Eloquent-query met ->when() en ->where().
     */
    public static function filter(?string $categorySlug = null, ?string $zoekterm = null, ?string $soort = null): Collection
    {
        $presets = static::all();

        if ($categorySlug) {
            $presets = $presets->filter(fn ($p) => $p->category->slug === $categorySlug);
        }

        if ($soort) {
            $presets = $presets->filter(fn ($p) => $p->soort === $soort);
        }

        if ($zoekterm) {
            $naald = mb_strtolower(trim($zoekterm));
            $presets = $presets->filter(function ($p) use ($naald) {
                return str_contains(mb_strtolower($p->name), $naald)
                    || str_contains(mb_strtolower($p->tagline), $naald)
                    || str_contains(mb_strtolower($p->category->name), $naald);
            });
        }

        // Packs en de bundel bovenaan: dat is wat je wilt verkopen.
        return $presets
            ->sortBy(fn ($p) => match ($p->soort) {
                'bundel' => 0,
                'pack' => 1,
                default => 2,
            })
            ->values();
    }

    /** Hoeveel producten er per categorie zijn, voor de tegels op de homepage. */
    public static function aantalPerCategorie(): Collection
    {
        return static::all()->groupBy('category_id')->map->count();
    }
}
