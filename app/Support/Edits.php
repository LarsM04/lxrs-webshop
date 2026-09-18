<?php

namespace App\Support;

use Illuminate\Support\Collection;

/**
 * De 'Recente edits'-sectie op de homepage.
 *
 * Leest de TikTok-links uit config/edits.php en zet ze om naar wat de view
 * nodig heeft: een embed-URL, een label en het account waar hij vandaan komt.
 */
class Edits
{
    /**
     * Haalt het videonummer uit een TikTok-link.
     *
     * https://www.tiktok.com/@lxrs2004/video/7301234567890123456 -> 7301234567890123456
     *
     * Lukt dat niet, dan geeft hij null terug en slaan we die link over.
     */
    public static function videoId(?string $url): ?string
    {
        if (! $url) {
            return null;
        }

        return preg_match('#/video/(\d+)#', $url, $m) ? $m[1] : null;
    }

    /** Het @account uit een TikTok-link, zonder de @. */
    public static function account(?string $url): ?string
    {
        return preg_match('#tiktok\.com/@([\w.]+)#', (string) $url, $m) ? $m[1] : null;
    }

    /**
     * Alle bruikbare edits uit de config. Links waar geen videonummer in zit
     * vallen er stilzwijgend uit, zodat een typfout de pagina niet sloopt.
     */
    public static function all(): Collection
    {
        $labels = config('edits.labels', []);

        return collect(config('edits.reels', []))
            ->map(fn ($url) => [
                'url' => $url,
                'id' => static::videoId($url),
                'account' => static::account($url),
            ])
            ->filter(fn ($edit) => $edit['id'] !== null)
            ->values()
            ->map(function ($edit, $i) use ($labels) {
                $edit['label'] = $labels ? $labels[$i % count($labels)] : null;
                $edit['embed'] = 'https://www.tiktok.com/embed/v2/' . $edit['id'];

                return (object) $edit;
            });
    }
}
