<?php

namespace App\Http\Controllers;

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
     *
     * Página de preview multi-superficie con selector, dark/light y GUI tema.
     */
    public function show(string $slug): Response
    {
        $manifest = $this->manifests->loadBySlug($slug);

        return Inertia::render('templates/preview', [
            'slug' => $slug,
            'manifest' => $manifest->toManifestArray(),
            'surfaces' => $manifest->types,
        ]);
    }

    /**
     * POST /templates/{slug}/preview
     *
     * Devuelve los datos que necesita el preview y el panel de personalización.
     * Valida que la selección de paleta del autor exista en el manifiesto.
     */
    public function data(Request $request, string $slug): JsonResponse
    {
        try {
            $manifest = $this->manifests->loadBySlug($slug);
        } catch (RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 404);
        }

        $paletteIds = [];

        if ($manifest->theme !== null) {
            $paletteIds = array_values(array_column($manifest->theme['palettes'] ?? [], 'id'));
        }

        $validated = $request->validate([
            'palette' => ['nullable', 'string', Rule::in($paletteIds)],
        ]);

        return response()->json([
            'manifest' => $manifest->toManifestArray(),
            'surfaces' => $manifest->types,
            'defaultPalette' => $manifest->theme['default'] ?? config('templates.fallback_palette.id'),
            'paletteIds' => $paletteIds,
            'palette' => $validated['palette'] ?? null,
        ]);
    }
}
