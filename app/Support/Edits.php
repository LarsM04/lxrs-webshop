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
            ->map(function ($edit) use ($labels) {
                // Label hoort bij het account, niet bij de volgorde. Anders
                // krijgt de tweede F1-edit op rij het label 'Film'.
                $edit['label'] = $labels[$edit['account']] ?? null;
                $edit['embed'] = 'https://www.tiktok.com/embed/v2/' . $edit['id'];

                // Lokale thumbnail en titel, opgehaald met `php artisan edits:thumbnails`.
                $meta = static::index()[$edit['id']] ?? null;
                $bestand = 'images/edits/' . $edit['id'] . '.jpg';
                $edit['thumb'] = file_exists(public_path($bestand)) ? $bestand : null;
                $edit['titel'] = $meta['titel'] ?? null;
                $edit['bijschrift'] = static::bijschrift($meta['titel'] ?? null);

                return (object) $edit;
            });
    }

    /**
     * TikTok-titels zien er zo uit:
     *
     *   "Can he win this weekend in monza?? || #formula1 #f1 ・ Upload Method → @editingnews.com"
     *
     * Het stuk dat je zelf typte staat altijd vooraan, tot de eerste hashtag
     * of de eerste ||. Al het andere is hashtags en credits, en dat wil je
     * niet als bijschrift op je eigen site.
     */
    public static function bijschrift(?string $titel): ?string
    {
        if (! $titel) {
            return null;
        }

        $schoon = preg_split('/\s*(\|\||#)/u', $titel)[0] ?? '';

        // Zero-width tekens komen mee uit TikTok en maken een lege regel niet leeg.
        $schoon = preg_replace('/[\x{200B}-\x{200D}\x{FEFF}]/u', '', $schoon);
        $schoon = trim(preg_replace('/\s{2,}/u', ' ', $schoon));

        return $schoon !== '' ? $schoon : null;
    }

    /** De opgehaalde titels, geschreven door het artisan-commando. */
    private static function index(): array
    {
        static $index = null;

        if ($index === null) {
            $pad = public_path('images/edits/index.json');
            $index = file_exists($pad) ? (json_decode(file_get_contents($pad), true) ?: []) : [];
        }

        return $index;
    }
}
