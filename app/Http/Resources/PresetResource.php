<?php

namespace App\Http\Resources;

use App\Models\Preset;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Hoe een preset er in de API uitziet.
 *
 * Door dit vast te leggen verandert de JSON niet stilletjes mee als er een
 * kolom bij de tabel komt, en lekken interne velden zoals download_path niet
 * naar buiten: dat is het bestand dat mensen straks moeten kopen.
 *
 * @mixin Preset
 */
class PresetResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'soort' => $this->soort,
            'aantal' => $this->aantal,
            'price' => (float) $this->price,
            'tagline' => $this->tagline,
            'description' => $this->description,
            'includes' => $this->includes,
            'bestandsgrootte' => $this->bestandsgrootte,
            'ae_version' => $this->ae_version,
            'is_featured' => $this->is_featured,
            'url' => route('presets.show', $this->slug),
            'category' => new CategoryResource($this->whenLoaded('category')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
