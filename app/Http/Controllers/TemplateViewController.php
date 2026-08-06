<?php

namespace App\Http\Controllers;

use App\Services\Templates\TemplateManifestService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * Sirve los artefactos (dist/, assets/, thumbnails/) desde el "home" del tema
 * con la URL canónica idéntica a la del sandbox / proyectos finales.
 */
class TemplateViewController extends Controller
{
    protected array $contentTypes = [
        'js' => 'application/javascript',
        'css' => 'text/css',
        'png' => 'image/png',
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'svg' => 'image/svg+xml',
        'json' => 'application/json',
        'woff' => 'font/woff',
        'woff2' => 'font/woff2',
    ];

    public function __construct(
        protected TemplateManifestService $manifests,
    ) {}

    /**
     * GET /templates/{slug}/{dir}/{file}
     *
     * Sirve un archivo bajo dist/ o assets/ del home del tema.
     */
    public function show(Request $request, string $slug, string $dir, string $file): BinaryFileResponse|Response
    {
        if (! in_array($dir, ['dist', 'assets', 'thumbnails'], true)) {
            return abort(404);
        }

        try {
            $manifest = $this->manifests->loadBySlug($slug);
        } catch (\RuntimeException) {
            return abort(404);
        }

        $home = $manifest->home;

        // Sin separadores de directorio: solo se sirven archivos planos en dist/.
        if ($dir === 'dist') {
            if (str_contains($file, '/') || str_contains($file, '\\') || $file !== basename($file)) {
                return abort(404);
            }
        }

        $path = $this->manifests->homePath($home).'/'.$dir.'/'.$file;

        if (! is_file($path)) {
            return abort(404);
        }

        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        $contentType = $this->contentTypes[$extension] ?? 'application/octet-stream';

        return response()->file($path, ['Content-Type' => $contentType]);
    }

    /**
     * GET /templates/{slug}/assets/{path}
     *
     * Sirve recursos anidados bajo assets/ (rutas canónicas en los TSX).
     */
    public function asset(Request $request, string $slug, string $path): BinaryFileResponse|Response
    {
        if (str_contains($path, '../') || str_contains($path, '..\\')) {
            return abort(404);
        }

        try {
            $manifest = $this->manifests->loadBySlug($slug);
        } catch (\RuntimeException) {
            return abort(404);
        }

        $real = realpath($this->manifests->homePath($manifest->home).'/assets/'.$path);

        if ($real === false) {
            return abort(404);
        }

        $assetsDir = realpath($this->manifests->homePath($manifest->home).'/assets');

        if ($assetsDir === false || ! str_starts_with($real, $assetsDir.DIRECTORY_SEPARATOR)) {
            return abort(404);
        }

        if (! is_file($real)) {
            return abort(404);
        }

        $extension = strtolower(pathinfo($real, PATHINFO_EXTENSION));
        $contentType = $this->contentTypes[$extension] ?? 'application/octet-stream';

        return response()->file($real, ['Content-Type' => $contentType]);
    }
}
