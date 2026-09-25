<?php

namespace App\Models;

use Database\Factories\PresetFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
