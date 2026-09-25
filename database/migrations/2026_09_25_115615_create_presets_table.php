<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * category_id is de koppeling met categories. Een categorie waar nog
     * presets in zitten kan niet weg (restrictOnDelete), zodat er nooit een
     * preset zonder categorie in de shop staat.
     */
    public function up(): void
    {
        Schema::create('presets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained()->restrictOnDelete();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('soort', 10)->default('los');
            $table->unsignedSmallInteger('aantal')->default(1);
            $table->decimal('price', 8, 2);
            $table->string('tagline');
            $table->text('description');
            $table->json('includes');
            $table->string('bestandsgrootte', 20)->nullable();
            $table->string('ae_version')->nullable();
            $table->string('image_path')->nullable();
            $table->string('download_path')->nullable();
            $table->boolean('is_featured')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('presets');
    }
};
