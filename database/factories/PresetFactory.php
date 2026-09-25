<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Preset;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Preset>
 */
class PresetFactory extends Factory
{
    /**
     * Define the model's default state: een losse preset.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = ucwords(fake()->unique()->words(3, true));

        return [
            'category_id' => Category::factory(),
            'name' => $name,
            'slug' => Str::slug($name),
            'soort' => 'los',
            'aantal' => 1,
            'price' => fake()->randomElement([2.5, 3, 4, 5]),
            'tagline' => fake()->sentence(4),
            'description' => fake()->paragraph(),
            'includes' => ['1 preset als .ffx'],
            'bestandsgrootte' => fake()->numberBetween(5, 900).' KB',
            'ae_version' => 'After Effects 2020 of nieuwer',
            'is_featured' => false,
        ];
    }

    /** Een pack: alle presets uit een categorie. */
    public function pack(int $aantal = 8): static
    {
        return $this->state(fn () => [
            'soort' => 'pack',
            'aantal' => $aantal,
            'price' => 14,
            'includes' => fake()->words($aantal),
        ]);
    }

    /** Het complete pack. */
    public function bundel(): static
    {
        return $this->state(fn () => [
            'soort' => 'bundel',
            'aantal' => 55,
            'price' => 39,
        ]);
    }

    /** Uitgelicht op de homepage. */
    public function featured(): static
    {
        return $this->state(fn () => ['is_featured' => true]);
    }
}
