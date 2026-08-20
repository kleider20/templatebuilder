<?php

namespace App\Http\Controllers\Templates;

use App\Actions\Templates\BuildEditorTreeAction;
use App\Actions\Templates\ReadTemplateFileAction;
use App\Actions\Templates\SaveTemplateFileAction;
use App\Actions\Templates\UpdateManifestAction;
use App\Actions\Templates\UploadThumbnailAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Templates\UpdateTemplateManifestRequest;
use App\Http\Requests\Templates\UpdateTemplateRequest;
use App\Http\Requests\Templates\UploadThumbnailRequest;
use App\Models\Template;
use App\Services\Templates\TemplateManifestService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use RuntimeException;

class TemplateEditorController extends Controller
{
    public function __construct(
        protected TemplateManifestService $manifests,
        protected BuildEditorTreeAction $tree,
        protected ReadTemplateFileAction $reader,
        protected SaveTemplateFileAction $savior,
        protected UpdateManifestAction $manifestAction,
        protected UploadThumbnailAction $thumbnail,
    ) {}

    /**
     * GET /templates/{slug}/editor
     */
    public function show(Request $request, string $slug): Response|RedirectResponse
    {
        try {
            $this->authorize('view', $this->templateFor($slug));
        } catch (\Throwable $e) {
            return to_route('dashboard.plantillas');
        }

        try {
            $manifest = $this->manifests->loadBySlug($slug);
        } catch (RuntimeException) {
            return to_route('dashboard.plantillas');
        }

        $tree = $this->tree->handle($manifest);

        $activePath = $request->query('path');

        $active = null;
        if (is_string($activePath) && $activePath !== '') {
            $active = $this->reader->handle($activePath, $manifest);
        }

        if ($active === null) {
            $active = $this->reader->handle($tree[0]->path ?? '', $manifest);
        }

        return Inertia::render('templates/editor', [
            'slug' => $slug,
            'manifest' => $manifest->toManifestArray(),
            'tree' => array_map(fn ($node) => $node->toArray(), $tree),
            'activeFile' => $active,
            'editorExtensions' => config('templates.editor_extensions'),
        ]);
    }

    /**
     * PUT /templates/{slug}/editor
     */
    public function update(UpdateTemplateRequest $request, string $slug): JsonResponse
    {
        try {
            $this->authorize('update', $this->templateFor($slug));
        } catch (\Throwable $e) {
            return response()->json([message => 'No autorizado.'], 403);
        }

        try {
            $manifest = $this->manifests->loadBySlug($slug);
        } catch (RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        $validated = $request->validated();

        try {
            $this->savior->handle($validated['path'], $validated['content'], $manifest);
        } catch (RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json(['message' => 'Archivo guardado.']);
    }

    /**
     * PUT /templates/{slug}/manifest
     */
    public function updateManifest(UpdateTemplateManifestRequest $request, string $slug): JsonResponse
    {
        try {
            $this->authorize('update', $this->templateFor($slug));
        } catch (\Throwable $e) {
            return response()->json(['message' => 'No autorizado.'], 403);
        }

        try {
            $manifest = $this->manifests->loadBySlug($slug);
        } catch (RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        $validated = $request->validated();

        try {
            $next = $this->manifestAction->handle(
                $manifest,
                $validated['settings'] ?? null,
                $validated['theme'] ?? null,
                $validated['pricing'] ?? null,
            );
        } catch (RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json(['message' => 'Manifiesto actualizado.', 'manifest' => $next]);
    }

    /**
     * POST /templates/{slug}/thumbnail
     */
    public function uploadThumbnail(UploadThumbnailRequest $request, string $slug): JsonResponse
    {
        try {
            $this->authorize('update', $this->templateFor($slug));
        } catch (\Throwable $e) {
            return response()->json(['message' => 'No autorizado.'], 403);
        }

        try {
            $manifest = $this->manifests->loadBySlug($slug);
        } catch (RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        $file = $request->file('file');

        if ($file === null) {
            return response()->json(['message' => 'Archivo requerido.'], 422);
        }

        return response()->json($this->thumbnail->handle($manifest, $file));
    }

    /**
     * Registro de propiedad del tema (para autorización) o un registro vacío.
     */
    protected function templateFor(string $slug): Template
    {
        return Template::firstOrNew(['slug' => $slug]);
    }
}
