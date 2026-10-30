<?php

namespace App\Models;

use Database\Factories\PresetFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

/**
 * Een product in de shop. Er zijn drie soorten:
 *   los     - een enkele .ffx-preset
 *   pack    - alle presets uit een categorie
 *   bundel  - het complete pack
 */
#[Fillable([
    'category_id', 'name', 'slug', 'soort', 'aantal', 'price', 'tagline', 'description',
    'includes', 'bestandsgrootte', 'ae_version', 'image_path', 'download_path', 'is_featured',
])]
class Preset extends Model
{
    /** @use HasFactory<PresetFactory> */
    use HasFactory;

    public const SOORTEN = ['los', 'pack', 'bundel'];

    /**
     * Standaardwaarden voor een nieuwe preset, voor als de API ze niet meestuurt.
     * aantal en is_featured staan ook als default in de migration, maar het
     * model moet ze direct na aanmaken al kennen, anders antwoordt de API met null.
     * includes heeft in de database geen default, dus die moet hier.
     *
     * @var array<string, mixed>
     */
    protected $attributes = [
        'aantal' => 1,
        'includes' => '[]',
        'is_featured' => false,
    ];

    /**
     * Gaat een preset weg, dan ook zijn afbeelding. Dat staat hier en niet in
     * een controller, zodat het ook gebeurt bij verwijderen via de API.
     */
    protected static function booted(): void
    {
        static::deleted(function (Preset $preset) {
            if ($preset->image_path) {
                Storage::disk('public')->delete($preset->image_path);
            }
        });
    }

    /**
     * De categorie waar deze preset bij hoort.
     *
     * @return BelongsTo<Category, $this>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'includes' => 'array',
            'is_featured' => 'boolean',
            'aantal' => 'integer',
        ];
    }
}
