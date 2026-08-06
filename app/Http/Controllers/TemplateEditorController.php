<?php

namespace App\Http\Controllers;

use App\Services\Templates\TemplateManifestService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Inertia\Inertia;
use Inertia\Response;
use RuntimeException;

class TemplateEditorController extends Controller
{
    public function __construct(
        protected TemplateManifestService $manifests,
    ) {}

    /**
     * GET /templates/{slug}/editor
     *
     * Muestra el editor con el árbol multi-raíz (una raíz por superficie del
     * tema: .tsx/.ts/.json/.css) y el contenido del archivo activo.
     */
    public function show(Request $request, string $slug): Response|RedirectResponse
    {
        try {
            $manifest = $this->manifests->loadBySlug($slug);
        } catch (RuntimeException $e) {
            return to_route('templates.index');
        }

        $tree = $this->tree($manifest);

        $activePath = $request->query('path');

        $active = null;
        if (is_string($activePath) && $activePath !== '') {
            $active = $this->readSafeFile($activePath, $manifest);
        }

        if ($active === null) {
            $active = $this->readSafeFile($tree[0]['path'] ?? '', $manifest);
        }

        return Inertia::render('templates/editor', [
            'slug' => $slug,
            'manifest' => $manifest->toManifestArray(),
            'tree' => $tree,
            'activeFile' => $active,
            'editorExtensions' => config('templates.editor_extensions'),
        ]);
    }

    /**
     * PUT /templates/{slug}/editor
     *
     * Guarda el contenido de un archivo con containment (realpath) y allowlist
     * de extensiones dentro de resources/js/layouts/.
     */
    public function update(Request $request, string $slug): JsonResponse
    {
        $validated = $request->validate([
            'path' => ['required', 'string'],
            'content' => ['required', 'string'],
        ]);

        try {
            $manifest = $this->manifests->loadBySlug($slug);
        } catch (RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        try {
            $absolute = $this->resolveSafePath($validated['path'], $manifest);
        } catch (RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        File::put($absolute, $validated['content']);

        return response()->json(['message' => 'Archivo guardado.']);
    }

    /**
     * POST /templates/{slug}/thumbnail
     *
     * Guarda la imagen de preview como {home}/thumbnails/preview.<ext> y
     * actualiza el campo thumbnail del template.json.
     */
    public function uploadThumbnail(Request $request, string $slug): JsonResponse
    {
        $request->validate([
            'file' => ['required', 'file', 'max:2048', 'mimetypes:image/png,image/jpeg,image/webp,image/gif'],
        ]);

        try {
            $manifest = $this->manifests->loadBySlug($slug);
        } catch (RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        $file = $request->file('file');

        if ($file === null) {
            return response()->json(['message' => 'Archivo requerido.'], 422);
        }

        $extension = strtolower($file->getClientOriginalExtension());
        $extension = in_array($extension, ['png', 'jpg', 'jpeg', 'webp', 'gif'], true) ? $extension : 'png';

        $thumbDir = $this->manifests->homePath($manifest->home).'/thumbnails';
        File::ensureDirectoryExists($thumbDir);

        $filename = 'preview.'.$extension;
        File::put($thumbDir.'/'.$filename, $file->get());

        // Actualizamos thumbnail en template.json (sin romper las demás claves).
        $current = $manifest->toManifestArray();
        $next = $current;
        $next['thumbnail'] = 'thumbnails/'.$filename;

        $manifestPath = $this->manifests->homePath($manifest->home).'/template.json';
        File::put($manifestPath, json_encode($next, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        return response()->json([
            'message' => 'Preview subida.',
            'thumbnail' => $next['thumbnail'],
        ]);
    }

    /**
     * Árbol multi-raíz del editor.
     *
     * @return array<int, array{name: string, type: string, path: string}>
     */
    protected function tree($manifest): array
    {
        $tree = [];

        foreach ($manifest->types as $surface) {
            $root = $this->manifests->basePath().'/'.$surface.'/templates/'.$manifest->slug;

            if (! is_dir($root)) {
                continue;
            }

            $tree = array_merge($tree, $this->walk($root, $root, $surface.'/templates/'.$manifest->slug));
        }

        return $tree;
    }

    /**
     * Recorre un directorio y devuelve nodos planos (para el árbol del editor).
     *
     * @return array<int, array{name: string, type: string, path: string}>
     */
    protected function walk(string $root, string $dir, string $prefix): array
    {
        $nodes = [];

        $entries = collect(File::allFiles($dir))
            ->sortBy(fn ($file) => $file->getFilename())
            ->values();

        foreach ($entries as $file) {
            $extension = $file->getExtension();

            if (! in_array($extension, (array) config('templates.editor_extensions'), true)) {
                continue;
            }

            $relative = str_replace('\\', '/', substr($file->getPathname(), strlen($root) + 1));
            $nodes[] = [
                'name' => $file->getFilename(),
                'type' => 'file',
                'path' => $prefix.'/'.$relative,
            ];
        }

        return $nodes;
    }

    /**
     * Lee un archivo de forma segura devolviendo contenido + path absoluto.
     *
     * @return array{path: string, content: string}|null
     */
    protected function readSafeFile(string $relPath, $manifest): ?array
    {
        try {
            $absolute = $this->resolveSafePath($relPath, $manifest);
        } catch (RuntimeException) {
            return null;
        }

        return [
            'path' => $relPath,
            'content' => File::get($absolute),
        ];
    }

    /**
     * Resuelve y valida un path relativo a un archivo dentro del tema:
     * realpath containment dentro de resources/js/layouts/, allowlist de
     * extensiones y sin "..".
     *
     * @throws RuntimeException
     */
    protected function resolveSafePath(string $relPath, $manifest): string
    {
        if (str_contains($relPath, '..')) {
            throw new RuntimeException('Ruta no permitida: "..".');
        }

        $normalized = str_replace('\\', '/', $relPath);

        $insideTheme = false;
        foreach ($manifest->types as $surface) {
            $expected = $surface.'/templates/'.$manifest->slug.'/';

            if (str_starts_with($normalized, $expected)) {
                $insideTheme = true;
                break;
            }
        }

        if (! $insideTheme) {
            throw new RuntimeException('La ruta debe pertenecer al tema.');
        }

        $absolute = realpath($this->manifests->basePath().'/'.$normalized);

        if ($absolute === false) {
            throw new RuntimeException('La ruta no existe en disco.');
        }

        $layoutsReal = realpath($this->manifests->basePath());

        if ($layoutsReal === false || ! str_starts_with($absolute, $layoutsReal.DIRECTORY_SEPARATOR)) {
            throw new RuntimeException('La ruta está fuera de layouts/.');
        }

        $extension = strtolower(pathinfo($absolute, PATHINFO_EXTENSION));

        if (! in_array($extension, (array) config('templates.editor_extensions'), true)) {
            throw new RuntimeException("Extensión [{$extension}] no permitida.");
        }

        return $absolute;
    }
}
