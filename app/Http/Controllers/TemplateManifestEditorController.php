<?php

namespace App\Http\Controllers;

use App\Services\Templates\TemplateManifestService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use RuntimeException;

/**
 * Edición del manifiesto para el autor: reescribe los bloques settings y
 * theme de template.json validándolos según la sección 3 del contrato.
 */
class TemplateManifestEditorController extends Controller
{
    public function __construct(
        protected TemplateManifestService $manifests,
    ) {}

    /**
     * PUT /templates/{slug}/manifest
     *
     * Acepta { settings?, theme? } y reescribe el template.json del home.
     */
    public function update(Request $request, string $slug): JsonResponse
    {
        $validated = $request->validate([
            'settings' => ['sometimes', 'array'],
            'theme' => ['sometimes', 'array'],
        ]);

        try {
            $manifest = $this->manifests->loadBySlug($slug);
        } catch (RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        $current = $manifest->toManifestArray();

        // Mutamos y validamos sobre una copia.
        $next = $current;

        if (array_key_exists('settings', $validated)) {
            $next['settings'] = $validated['settings'];
        }

        if (array_key_exists('theme', $validated)) {
            $next['theme'] = $validated['theme'];
        }

        // Si se pasa theme, requerimos sustitución completa válida.
        try {
            $this->manifests->validate($next);
        } catch (RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        $manifestPath = $this->manifests->homePath($manifest->home).'/template.json';

        File::put($manifestPath, json_encode($next, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        return response()->json(['message' => 'Manifiesto actualizado.', 'manifest' => $next]);
    }
}
