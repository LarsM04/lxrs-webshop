<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\PresetRequest;
use Illuminate\Support\Str;

/**
 * Toevoegen en bewerken van een preset via het formulier in de beheeromgeving.
 *
 * De regels zijn dezelfde als die van de API (PresetRequest). Een HTML-formulier
 * stuurt alleen alles als tekst, dus hier zetten we eerst om wat de API al in de
 * goede vorm krijgt:
 *
 * - het tekstvak "wat je krijgt" (één regel per item) wordt een lijst
 * - het vinkje "uitgelicht" wordt true of false; zonder vinkje stuurt de
 *   browser het veld helemaal niet mee
 * - een lege slug wordt gemaakt van de naam, ook bij bewerken
 *
 * Wie dit mag, regelt de auth-middleware in routes/web.php.
 */
class PresetFormRequest extends PresetRequest
{
    protected function prepareForValidation(): void
    {
        $includes = $this->input('includes');

        $this->merge([
            'includes' => Str::of(is_string($includes) ? $includes : '')
                ->explode("\n")
                ->map(fn (string $regel) => trim($regel))
                ->filter()
                ->values()
                ->all(),
            'is_featured' => $this->boolean('is_featured'),
        ]);

        if (blank($this->input('slug')) && filled($this->input('name'))) {
            $this->merge(['slug' => Str::slug($this->input('name'))]);
        }
    }
}
