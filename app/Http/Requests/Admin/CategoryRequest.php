<?php

namespace App\Http\Requests\Admin;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

/**
 * Toevoegen en bewerken van een categorie in de beheeromgeving.
 *
 * Wie dit mag, regelt de auth-middleware in routes/web.php; daarom geeft
 * authorize() gewoon true terug.
 */
class CategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** Een lege slug maken we van de naam: "Color Grades" wordt "color-grades". */
    protected function prepareForValidation(): void
    {
        if (blank($this->input('slug')) && filled($this->input('name'))) {
            $this->merge(['slug' => Str::slug($this->input('name'))]);
        }
    }

    /**
     * De maximale lengtes volgen de kolommen in de migration.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'alpha_dash:ascii', Rule::unique('categories', 'slug')->ignore($this->route('category'))],
            'afkorting' => ['required', 'string', 'max:20'],
            'omschrijving' => ['required', 'string', 'max:255'],
        ];
    }
}
