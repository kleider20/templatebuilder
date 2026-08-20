<?php

namespace App\Services\Templates;

use App\Data\Templates\TemplateManifestData;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Process;
use RuntimeException;

/**
 * Compilación de temas a dist/. Reutilizado por el importador y por la
 * aplicación de un tema para asegurar que dist/{slug}.js|css existan.
 */
class TemplateBuildService
{
    public function __construct(
        protected TemplateManifestService $manifests,
    ) {}

    /**
     * Recompila dist/ del tema si no trae los artefactos compilados.
     *
     * @throws RuntimeException
     */
    public function ensureBuilt(TemplateManifestData $manifest): void
    {
        $distDir = $this->manifests->homePath($manifest->home).'/dist';
        $jsFile = $distDir.'/'.basename($manifest->exports['bundle']);
        $cssFile = $distDir.'/'.basename($manifest->exports['styles']);

        if (is_file($jsFile) && is_file($cssFile)) {
            return;
        }

        $script = base_path('bin/build-template.mjs');

        if (! is_file($script)) {
            throw new RuntimeException('El tema no trae dist/ compilado y falta bin/build-template.mjs.');
        }

        $process = Process::path(base_path())->timeout(120)->run(['node', $script, $manifest->slug]);

        if (! $process->successful()) {
            throw new RuntimeException('La compilación del tema falló: '.$process->errorOutput());
        }
    }
}