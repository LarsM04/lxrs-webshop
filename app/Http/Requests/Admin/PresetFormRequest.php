<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\PresetRequest;
use Illuminate\Contracts\Validation\ValidationRule;
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
 * Daarnaast kan het formulier een afbeelding uploaden; dat kan de API niet.
 *
 * Wie dit mag, regelt de auth-middleware in routes/web.php.
 */
class PresetFormRequest extends PresetRequest
{
    /** Maximale grootte van een afbeelding, in kilobytes (2 MB). */
    public const MAX_AFBEELDING_KB = 2048;

    /**
     * De regels van de API, plus de afbeelding.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            ...parent::rules(),
            'afbeelding' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:'.self::MAX_AFBEELDING_KB],
            'afbeelding_weghalen' => ['boolean'],
        ];
    }

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
            'afbeelding_weghalen' => $this->boolean('afbeelding_weghalen'),
        ]);

        if (blank($this->input('slug')) && filled($this->input('name'))) {
            $this->merge(['slug' => Str::slug($this->input('name'))]);
        }
    }
}
