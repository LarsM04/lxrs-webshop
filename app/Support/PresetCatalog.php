<?php

namespace App\Support;

use App\Models\Category;
use App\Models\Preset;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

/**
 * De catalogus van het LXRS Edit Pack, live uit de database.
 *
 * Tot fase 1 kwam alles uit een PHP-array. Nu zitten de producten in de
 * tabellen categories en presets (zie database/seeders), maar de methodes
 * hieronder heten nog precies hetzelfde. Daardoor hoefden de controllers en
 * Blade-views niet mee te veranderen.
 *
 * Er zijn drie soorten producten:
 *   los     - een enkele .ffx-preset
 *   pack    - alle presets uit een categorie
 *   bundel  - het complete pack
 */
class PresetCatalog
{
    /** @return Collection<int, Category> */
    public static function categories(): Collection
    {
        return Category::query()->orderBy('id')->get();
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

    public static function findCategory(?string $slug): ?Category
    {
        return $slug ? Category::query()->where('slug', $slug)->first() : null;
    }

    /** @return Collection<int, Preset> */
    public static function all(): Collection
    {
        return Preset::query()->with('category')->orderBy('id')->get();
    }

    /** Wat er op de homepage uitgelicht staat: de bundel en een paar packs. */
    public static function featured(): Collection
    {
        return static::gesorteerd(Preset::query()->with('category')->where('is_featured', true))->get();
    }

    public static function findBySlug(string $slug): ?Preset
    {
        return Preset::query()->with('category')->where('slug', $slug)->first();
    }

    /**
     * Wat er bij een product past.
     *
     * Bij een los product: andere losse presets uit dezelfde categorie.
     * Bij een pack of bundel: wat erin zit, oftewel de losse presets zelf.
     */
    public static function related(Preset $preset, int $limit = 4): Collection
    {
        $zelfdeCategorie = fn () => Preset::query()
            ->with('category')
            ->whereBelongsTo($preset->category)
            ->whereKeyNot($preset->id);

        if ($preset->soort === 'los') {
            // Het pack van deze categorie eerst: dat is de logische upsell.
            $pack = $zelfdeCategorie()->where('soort', 'pack')->first();

            return collect([$pack])
                ->filter()
                ->concat($zelfdeCategorie()->where('soort', 'los')->inRandomOrder()->limit($limit)->get())
                ->take($limit)
                ->values();
        }

        if ($preset->soort === 'pack') {
            return $zelfdeCategorie()->where('soort', 'los')->orderBy('id')->limit($limit)->get();
        }

        // De bundel: laat de packs zien die erin zitten.
        return Preset::query()->with('category')->where('soort', 'pack')->orderBy('id')->limit($limit)->get();
    }

    /** Filteren op categorie, soort en zoekterm. */
    public static function filter(?string $categorySlug = null, ?string $zoekterm = null, ?string $soort = null): Collection
    {
        $query = Preset::query()
            ->with('category')
            ->when($categorySlug, fn (Builder $q) => $q->whereRelation('category', 'slug', $categorySlug))
            ->when($soort, fn (Builder $q) => $q->where('soort', $soort))
            ->when($zoekterm, function (Builder $q) use ($zoekterm) {
                $naald = '%'.trim($zoekterm).'%';

                // Tussen haakjes, anders gaat de OR voor de filters hierboven.
                $q->where(fn (Builder $q) => $q
                    ->whereLike('name', $naald)
                    ->orWhereLike('tagline', $naald)
                    ->orWhereRelation('category', 'name', 'like', $naald));
            });

        return static::gesorteerd($query)->get();
    }

    /** Hoeveel producten er per categorie zijn, voor de tegels op de homepage. */
    public static function aantalPerCategorie(): Collection
    {
        return Preset::query()
            ->selectRaw('category_id, count(*) as aantal')
            ->groupBy('category_id')
            ->pluck('aantal', 'category_id');
    }

    /** Packs en de bundel bovenaan: dat is wat je wilt verkopen. */
    private static function gesorteerd(Builder $query): Builder
    {
        return $query
            ->orderByRaw("case soort when 'bundel' then 0 when 'pack' then 1 else 2 end")
            ->orderBy('id');
    }
}
