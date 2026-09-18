<?php

namespace App\Support;

use Illuminate\Support\Collection;

/**
 * Tijdelijke catalogus met vaste data.
 *
 * In fase 1 wordt de binnenkant van deze klasse vervangen door Eloquent
 * (Preset::with('category')->get() en zo). De buitenkant blijft hetzelfde,
 * dus de Blade-views hoeven dan niet mee te veranderen: ze gebruiken nu al
 * objecten met -> in plaats van arrays met [].
 */
class PresetCatalog
{
    /** Alle categorieen, in de volgorde waarin ze op de site staan. */
    public static function categories(): Collection
    {
        return collect([
            ['id' => 1, 'slug' => 'color-corrections', 'name' => "Color Corrections", 'afkorting' => "CC's",
             'omschrijving' => 'Kleurgrades die je hele edit in een klap laten kloppen.'],
            ['id' => 2, 'slug' => 'text-presets', 'name' => 'Text Presets', 'afkorting' => 'Text',
             'omschrijving' => 'Titels en kickers die op de beat binnenkomen.'],
            ['id' => 3, 'slug' => 'shakes', 'name' => 'Shakes', 'afkorting' => 'Shakes',
             'omschrijving' => 'Camera shakes met gewicht, zonder dat het misselijk maakt.'],
            ['id' => 4, 'slug' => 'zooms', 'name' => 'Zooms', 'afkorting' => 'Zooms',
             'omschrijving' => 'Velocity zooms en punch-ins, klaar om te slepen.'],
        ])->map(fn ($c) => (object) $c);
    }

    public static function findCategory(?string $slug): ?object
    {
        return $slug ? static::categories()->firstWhere('slug', $slug) : null;
    }

    /** Alle presets, met hun categorie eraan gekoppeld. */
    public static function all(): Collection
    {
        $categories = static::categories()->keyBy('id');

        return collect(static::data())
            ->map(function ($p) use ($categories) {
                $p['category'] = $categories->get($p['category_id']);

                return (object) $p;
            });
    }

    public static function featured(): Collection
    {
        return static::all()->where('is_featured', true)->values();
    }

    public static function findBySlug(string $slug): ?object
    {
        return static::all()->firstWhere('slug', $slug);
    }

    /** Presets uit dezelfde categorie, zonder de preset zelf. */
    public static function related(object $preset, int $limit = 3): Collection
    {
        return static::all()
            ->where('category_id', $preset->category_id)
            ->where('id', '!=', $preset->id)
            ->take($limit)
            ->values();
    }

    /**
     * Filteren op categorie en zoekterm. Dit is precies wat in fase 2 een
     * Eloquent-query wordt met ->where() en ->when().
     */
    public static function filter(?string $categorySlug = null, ?string $zoekterm = null): Collection
    {
        $presets = static::all();

        if ($categorySlug) {
            $presets = $presets->filter(fn ($p) => $p->category->slug === $categorySlug);
        }

        if ($zoekterm) {
            $naald = mb_strtolower(trim($zoekterm));
            $presets = $presets->filter(function ($p) use ($naald) {
                return str_contains(mb_strtolower($p->name), $naald)
                    || str_contains(mb_strtolower($p->description), $naald)
                    || str_contains(mb_strtolower($p->category->name), $naald);
            });
        }

        return $presets->values();
    }

    /** De ruwe rijen — dit wordt straks de seeder. */
    private static function data(): array
    {
        return [
            [
                'id' => 1, 'category_id' => 1, 'slug' => 'neon-grade',
                'name' => 'Neon Grade', 'price' => 14.00, 'is_featured' => true,
                'tagline' => 'De look uit m\'n F1-edits',
                'description' => 'De grade die onder bijna al m\'n race-edits ligt: diepe blauwzwarte schaduwen, '
                    . 'magenta in de highlights en net genoeg contrast om beelden van slechte kwaliteit te laten werken. '
                    . 'Sleep \'m op je adjustment layer en draai aan een schuif.',
                'includes' => ['8 grades als .aep', '3 adjustment-varianten', 'LUT-versie (.cube)'],
                'ae_version' => 'After Effects 2021 of nieuwer',
                'bestandsgrootte' => '24 MB',
            ],
            [
                'id' => 2, 'category_id' => 1, 'slug' => 'film-burn-cc',
                'name' => 'Film Burn CC', 'price' => 12.00, 'is_featured' => false,
                'tagline' => 'Warme filmlook met korrel',
                'description' => 'Zachte halation, warme korrel en een lichte lift in de zwarten. Gemaakt voor '
                    . 'filmedits waar het beeld moet ademen in plaats van knallen.',
                'includes' => ['6 grades als .aep', 'Korrel-overlay (4K)', 'Halation-preset'],
                'ae_version' => 'After Effects 2020 of nieuwer',
                'bestandsgrootte' => '48 MB',
            ],
            [
                'id' => 3, 'category_id' => 1, 'slug' => 'midnight-teal',
                'name' => 'Midnight Teal', 'price' => 12.00, 'is_featured' => false,
                'tagline' => 'Koel en clean',
                'description' => 'Strakke teal-orange zonder dat huidtinten oranje worden. Werkt goed op '
                    . 'avondbeelden en stadionlicht.',
                'includes' => ['5 grades als .aep', 'Skin-protect masker'],
                'ae_version' => 'After Effects 2021 of nieuwer',
                'bestandsgrootte' => '18 MB',
            ],
            [
                'id' => 4, 'category_id' => 2, 'slug' => 'impact-kicker',
                'name' => 'Impact Kicker', 'price' => 16.00, 'is_featured' => true,
                'tagline' => 'Titels die op de beat landen',
                'description' => 'Tien teksten die inkomen met een snap, een lichte overshoot en een blur die '
                    . 'precies op de eerste frame zit. Je zet je eigen tekst erin en de timing blijft kloppen.',
                'includes' => ['10 tekstanimaties', 'In- en uit-varianten', 'Handleiding (pdf)'],
                'ae_version' => 'After Effects 2022 of nieuwer',
                'bestandsgrootte' => '12 MB',
            ],
            [
                'id' => 5, 'category_id' => 2, 'slug' => 'type-snap',
                'name' => 'Type Snap', 'price' => 11.00, 'is_featured' => false,
                'tagline' => 'Letter voor letter, strak getimed',
                'description' => 'Typemachine-animaties met variabele snelheid, plus een cursor die je aan of '
                    . 'uit kunt zetten. Handig voor quotes en commentaar-edits.',
                'includes' => ['7 tekstanimaties', 'Cursor-preset'],
                'ae_version' => 'After Effects 2020 of nieuwer',
                'bestandsgrootte' => '6 MB',
            ],
            [
                'id' => 6, 'category_id' => 3, 'slug' => 'beat-shake',
                'name' => 'Beat Shake', 'price' => 9.00, 'is_featured' => true,
                'tagline' => 'Shakes met gewicht',
                'description' => 'Twaalf camera shakes van subtiel tot hard, allemaal met een echte afname in '
                    . 'plaats van een loop. Zet \'m op je adjustment layer en stem de sterkte af op je beat.',
                'includes' => ['12 shakes als .aep', 'Loopbare varianten', 'Sterkte-schuif'],
                'ae_version' => 'After Effects 2020 of nieuwer',
                'bestandsgrootte' => '4 MB',
            ],
            [
                'id' => 7, 'category_id' => 3, 'slug' => 'impact-shake',
                'name' => 'Impact Shake', 'price' => 9.00, 'is_featured' => false,
                'tagline' => 'Voor die ene frame',
                'description' => 'Korte, harde klappen voor momenten waar het beeld even moet schrikken. '
                    . 'Zes varianten, van tik tot dreun.',
                'includes' => ['6 shakes als .aep'],
                'ae_version' => 'After Effects 2020 of nieuwer',
                'bestandsgrootte' => '3 MB',
            ],
            [
                'id' => 8, 'category_id' => 4, 'slug' => 'velocity-zoom',
                'name' => 'Velocity Zoom', 'price' => 13.00, 'is_featured' => true,
                'tagline' => 'Zoom met motion blur die klopt',
                'description' => 'Zooms met echte directional blur in plaats van een gauss-waas. Acht snelheden, '
                    . 'van trage push-in tot een zoom die je nauwelijks ziet gebeuren.',
                'includes' => ['8 zooms als .aep', 'Blur-controle', 'In- en uit-varianten'],
                'ae_version' => 'After Effects 2021 of nieuwer',
                'bestandsgrootte' => '9 MB',
            ],
            [
                'id' => 9, 'category_id' => 4, 'slug' => 'punch-zoom',
                'name' => 'Punch Zoom', 'price' => 9.00, 'is_featured' => false,
                'tagline' => 'Eén frame, volle klap',
                'description' => 'De klassieke punch-in op de beat, met een lichte overshoot zodat het niet '
                    . 'mechanisch aanvoelt. Vijf sterktes.',
                'includes' => ['5 zooms als .aep'],
                'ae_version' => 'After Effects 2020 of nieuwer',
                'bestandsgrootte' => '3 MB',
            ],
        ];
    }
}
