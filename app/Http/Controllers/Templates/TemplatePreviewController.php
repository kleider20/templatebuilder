<?php

namespace App\Http\Controllers\Templates;

use App\Data\Templates\TemplatePreviewData;
use App\Http\Controllers\Controller;
use App\Models\Template;
use App\Services\Templates\TemplateManifestService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;
use RuntimeException;

/**
 * Preview del tema y datos de personalización para el autor.
 */
class TemplatePreviewController extends Controller
{
    public function __construct(
        protected TemplateManifestService $manifests,
    ) {}

    /**
     * GET /templates/{slug}/preview
     */
    public function show(Request $request, string $slug): Response
    {
        $this->authorize('view', $this->templateFor($slug));

        try {
            $manifest = $this->manifests->loadBySlug($slug);
        } catch (RuntimeException) {
            abort(404);
        }

        return Inertia::render('templates/preview', [
            'slug' => $slug,
            'manifest' => $manifest->toManifestArray(),
            'surfaces' => $manifest->types,
        ]);
    }

    /**
     * POST /templates/{slug}/preview
     */
    public function data(Request $request, string $slug): JsonResponse
    {
        $this->authorize('view', $this->templateFor($slug));

        try {
            $manifest = $this->manifests->loadBySlug($slug);
        } catch (RuntimeException) {
            return response()->json(['message' => 'Tema no encontrado.'], 404);
        }

        $paletteIds = [];

        if ($manifest->theme !== null) {
            $paletteIds = array_values(array_column($manifest->theme['palettes'] ?? [], 'id'));
        }

        $validated = $request->validate([
            'palette' => ['nullable', 'string', Rule::in($paletteIds)],
        ]);

        $preview = new TemplatePreviewData(
            manifest: $manifest->toManifestArray(),
            surfaces: $manifest->types,
            defaultPalette: $manifest->theme['default'] ?? config('templates.fallback_palette.id'),
            paletteIds: $paletteIds,
            palette: $validated['palette'] ?? null,
        );

        return response()->json($preview->toArray());
    }

    protected function templateFor(string $slug): Template
    {
        return Template::firstOrNew(['slug' => $slug]);
    }
}
