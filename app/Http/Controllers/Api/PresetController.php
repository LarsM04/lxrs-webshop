<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\PresetRequest;
use App\Http\Resources\PresetResource;
use App\Models\Preset;
use App\Support\PresetCatalog;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

/**
 * De API voor presets: dezelfde data als de website, maar als JSON.
 *
 * Lezen mag iedereen. Toevoegen, bewerken en verwijderen kan alleen met de
 * API-sleutel; dat regelt de EnsureApiKey-middleware in routes/api.php.
 */
class PresetController extends Controller
{
    /**
     * GET /api/presets — alle presets, met dezelfde filters als de website:
     * ?categorie=zooms, ?soort=pack en ?zoek=glow.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $soort = $request->query('soort');

        $presets = PresetCatalog::filter(
            $request->query('categorie'),
            trim((string) $request->query('zoek', '')) ?: null,
            in_array($soort, Preset::SOORTEN, true) ? $soort : null,
        );

        return PresetResource::collection($presets);
    }

    /** POST /api/presets — antwoordt met 201 Created en de nieuwe preset. */
    public function store(PresetRequest $request): PresetResource
    {
        $preset = Preset::create($request->validated());

        return new PresetResource($preset->load('category'));
    }

    /** GET /api/presets/{id} */
    public function show(Preset $preset): PresetResource
    {
        return new PresetResource($preset->load('category'));
    }

    /** PUT of PATCH /api/presets/{id} */
    public function update(PresetRequest $request, Preset $preset): PresetResource
    {
        $preset->update($request->validated());

        return new PresetResource($preset->load('category'));
    }

    /** DELETE /api/presets/{id} — antwoordt met 204 No Content. */
    public function destroy(Preset $preset): Response
    {
        $preset->delete();

        return response()->noContent();
    }
}
