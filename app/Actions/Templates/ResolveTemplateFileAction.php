<?php

namespace App\Actions\Templates;

use App\Data\Templates\TemplateManifestData;
use App\Services\Templates\TemplateManifestService;
use RuntimeException;

class ResolveTemplateFileAction
{
    public function __construct(
        protected TemplateManifestService $manifests,
    ) {}

    /**
     * Resuelve y valida un path relativo a un archivo dentro del tema:
     * realpath containment dentro de resources/js/layouts/, allowlist de
     * extensiones y sin "..".
     *
     * @throws RuntimeException
     */
    public function handle(string $relPath, TemplateManifestData $manifest): string
    {
        if (str_contains($relPath, '..')) {
            throw new RuntimeException('Ruta no permitida: "..".');
        }

        $normalized = str_replace('\\', '/', $relPath);

        $expected = $manifest->home.'/';

        if (! str_starts_with($normalized, $expected)) {
            throw new RuntimeException('La ruta debe pertenecer al tema.');
        }

        $base = $this->manifests->basePath();

        $absolute = realpath($base.'/'.$normalized);

        if ($absolute === false) {
            throw new RuntimeException('La ruta no existe en disco.');
        }

        $layoutsReal = realpath($base);

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
