<?php

namespace App\Console\Commands;

use App\Support\Edits;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

/**
 * Haalt van elke edit in config/edits.php de thumbnail en titel op bij TikTok
 * en zet die lokaal neer, zodat de homepage niks van TikTok hoeft te laden
 * voordat een bezoeker op play drukt.
 *
 *   php artisan edits:thumbnails
 *
 * Draai dit opnieuw zodra je een link toevoegt of vervangt.
 */
class EditsThumbnails extends Command
{
    protected $signature = 'edits:thumbnails {--force : Ook opnieuw ophalen wat er al staat}';

    protected $description = 'Haalt thumbnails en titels van de TikTok-edits op';

    public function handle(): int
    {
        $map = public_path('images/edits');
        if (! is_dir($map)) {
            mkdir($map, 0755, true);
        }

        $indexPad = $map . '/index.json';
        $index = file_exists($indexPad)
            ? json_decode(file_get_contents($indexPad), true) ?: []
            : [];

        $edits = Edits::all();

        if ($edits->isEmpty()) {
            $this->warn('Geen edits in config/edits.php. Niets te doen.');

            return self::SUCCESS;
        }

        foreach ($edits as $edit) {
            $bestand = $map . '/' . $edit->id . '.jpg';

            if (! $this->option('force') && file_exists($bestand) && isset($index[$edit->id])) {
                $this->line("  = {$edit->id} staat er al");
                continue;
            }

            $this->line("  > {$edit->id} ophalen...");

            try {
                $meta = Http::timeout(20)
                    ->get('https://www.tiktok.com/oembed', ['url' => $edit->url])
                    ->throw()
                    ->json();

                $plaatje = Http::timeout(30)->get($meta['thumbnail_url'])->throw()->body();
                $this->verkleinEnBewaar($plaatje, $bestand);

                $index[$edit->id] = [
                    'titel' => $meta['title'] ?? '',
                    'auteur' => $meta['author_name'] ?? '',
                    'opgehaald' => now()->toDateString(),
                ];

                $kb = round(filesize($bestand) / 1024);
                $this->info("    opgeslagen ({$kb} KB)");
            } catch (\Throwable $e) {
                $this->error("    mislukt: " . $e->getMessage());
            }
        }

        file_put_contents($indexPad, json_encode($index, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        $this->newLine();
        $this->info(count($index) . ' edits in ' . str_replace(base_path() . DIRECTORY_SEPARATOR, '', $indexPad));

        return self::SUCCESS;
    }

    /** TikTok levert 1080x1080; dat is veel te zwaar voor een kaartje. */
    private function verkleinEnBewaar(string $data, string $pad): void
    {
        $bron = imagecreatefromstring($data);
        $breedte = imagesx($bron);
        $hoogte = imagesy($bron);

        $doelBreedte = 540;
        $doelHoogte = (int) round($hoogte * ($doelBreedte / $breedte));

        $doel = imagecreatetruecolor($doelBreedte, $doelHoogte);
        imagecopyresampled($doel, $bron, 0, 0, 0, 0, $doelBreedte, $doelHoogte, $breedte, $hoogte);
        imagejpeg($doel, $pad, 82);

        imagedestroy($bron);
        imagedestroy($doel);
    }
}
