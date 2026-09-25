<?php

namespace App\Http\Requests;

use App\Models\Preset;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

/**
 * Validatie voor toevoegen (POST) en bewerken (PUT/PATCH) van een preset.
 *
 * - POST en PUT sturen de hele preset, dus de verplichte velden moeten erin.
 * - PATCH stuurt alleen wat verandert; elk veld is dan optioneel.
 *
 * Wie dit mag, regelt de EnsureApiKey-middleware; daarom geeft authorize()
 * gewoon true terug.
 */
class PresetRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Zonder slug maken we er een van de naam, alleen bij toevoegen.
     * "Cold CC" wordt dan "cold-cc".
     */
    protected function prepareForValidation(): void
    {
        if ($this->isMethod('post') && blank($this->input('slug')) && filled($this->input('name'))) {
            $this->merge(['slug' => Str::slug($this->input('name'))]);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $preset = $this->route('preset');

        $rules = [
            'category_id' => ['required', 'integer', Rule::exists('categories', 'id')],
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'alpha_dash:ascii', Rule::unique('presets', 'slug')->ignore($preset)],
            'soort' => ['required', Rule::in(Preset::SOORTEN)],
            'aantal' => ['integer', 'min:1', 'max:1000'],
            'price' => ['required', 'numeric', 'decimal:0,2', 'min:0', 'max:9999'],
            'tagline' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:5000'],
            'includes' => ['array', 'max:100'],
            'includes.*' => ['string', 'max:255'],
            'bestandsgrootte' => ['nullable', 'string', 'max:20'],
            'ae_version' => ['nullable', 'string', 'max:255'],
            'is_featured' => ['boolean'],
        ];

        if ($this->isMethod('patch')) {
            $rules = array_map(fn (array $regels) => ['sometimes', ...$regels], $rules);
        }

        return $rules;
    }
}
