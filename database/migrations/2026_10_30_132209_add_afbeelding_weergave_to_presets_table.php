<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Hoe de afbeelding in het kader staat. Kaart en productpagina zijn
     * allebei 16:10, dus één instelling geldt voor allebei.
     *
     * afbeelding_x / _y    welk deel in beeld is, in procenten (50 = midden)
     * afbeelding_zoom      100 = normaal, 200 = twee keer zo groot
     * afbeelding_passend   true = hele afbeelding tonen, niets afsnijden
     */
    public function up(): void
    {
        Schema::table('presets', function (Blueprint $table) {
            $table->unsignedTinyInteger('afbeelding_x')->default(50)->after('image_path');
            $table->unsignedTinyInteger('afbeelding_y')->default(50)->after('afbeelding_x');
            $table->unsignedSmallInteger('afbeelding_zoom')->default(100)->after('afbeelding_y');
            $table->boolean('afbeelding_passend')->default(false)->after('afbeelding_zoom');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('presets', function (Blueprint $table) {
            $table->dropColumn(['afbeelding_x', 'afbeelding_y', 'afbeelding_zoom', 'afbeelding_passend']);
        });
    }
};
