<?php

namespace App\Services\Templates;

use App\Models\TemplateVersion;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Process;
use RuntimeException;
use ZipArchive;

/**
 * Compila (build-template.mjs) y empaqueta un tema en un ZIP verbatim
 * bajo output/ registrando una TemplateVersion.
 */
class TemplateExportService
{
    public function __construct(
        protected TemplateManifestService $manifests,
    ) {}

    /**
     * Exporta un tema: compila dist/ y crea output/{slug}-{version}.zip.
     *
     * @throws RuntimeException
     */
    public function export(string $slug, ?int $userId = null): TemplateVersion
    {
        $manifest = $this->manifests->loadBySlug($slug);

        $this->runBuild($slug);

        $distDir = $this->manifests->homePath($manifest->home).'/dist';
        $jsFile = $distDir.'/'.basename($manifest->exports['bundle']);
        $cssFile = $distDir.'/'.basename($manifest->exports['styles']);

        if (! is_file($jsFile)) {
            throw new RuntimeException("Falta el artefacto JS [{$manifest->exports['bundle']}].");
        }

        if (! is_file($cssFile)) {
            throw new RuntimeException("Falta el artefacto CSS [{$manifest->exports['styles']}].");
        }

        $this->assertBundleIntegrity($jsFile);
        $this->assertCssIntegrity($cssFile);

        $outputDir = base_path((string) config('templates.output_path'));

        File::ensureDirectoryExists($outputDir);

        $zipName = "{$slug}-{$manifest->version}.zip";
        $zipPath = $outputDir.'/'.$zipName;

        $this->zipVerbatim($manifest, $zipPath);

        $checksum = hash_file('sha256', $zipPath);

        return TemplateVersion::create([
            'slug' => $slug,
            'version' => $manifest->version,
            'manifest' => $manifest->toManifestArray(),
            'zip_path' => 'output/'.$zipName,
            'checksum' => $checksum,
            'user_id' => $userId,
        ]);
    }

    /**
     * Ejecuta bin/build-template.mjs vía Process (argumentos en array).
     *
     * @throws RuntimeException
     */
    protected function runBuild(string $slug): void
    {
        $script = base_path('bin/build-template.mjs');

        if (! is_file($script)) {
            throw new RuntimeException('Falta bin/build-template.mjs.');
        }

        $process = Process::path(base_path())->timeout(120)->run(['node', $script, $slug]);

        if (! $process->successful()) {
            throw new RuntimeException('La compilación del tema falló: '.$process->errorOutput());
        }
    }

    /**
     * Valida que el bundle JS mantenga el external react y las claves por superficie.
     *
     * @throws RuntimeException
     */
    protected function assertBundleIntegrity(string $jsFile): void
    {
        $content = File::get($jsFile);

        if (! str_contains($content, 'from"react') && ! str_contains($content, 'react/jsx-runtime')) {
            throw new RuntimeException('El bundle no conserva React como external.');
        }

        $manifest = $this->currentManifestFor($jsFile);

        foreach ($manifest->types as $surface) {
            if (! str_contains($content, $surface)) {
                throw new RuntimeException("El bundle no contiene la clave [{$surface}].");
            }
        }
    }

    /**
     * Valida que el CSS contenga el mapeo de roles y la variante dark.
     *
     * @throws RuntimeException
     */
    protected function assertCssIntegrity(string $cssFile): void
    {
        $content = File::get($cssFile);

        if (! str_contains($content, '--color-primary: var(--tpl-primary)')) {
            throw new RuntimeException('El CSS no contiene el mapeo --color-primary.');
        }

        if (! str_contains($content, '.dark')) {
            throw new RuntimeException('El CSS no contiene la variante dark.');
        }
    }

    /**
     * Relee el manifiesto desde el home que contiene el dist/.
     */
    protected function currentManifestFor(string $jsFile): TemplateManifestDto
    {
        $home = basename(dirname(dirname($jsFile)));
        $slug = $home;

        return $this->manifests->loadBySlug($slug);
    }

    /**
     * Empaqueta verbatim: template.json + cada superficie del tema.
     *
     * @throws RuntimeException
     */
    protected function zipVerbatim(TemplateManifestDto $manifest, string $zipPath): void
    {
        @unlink($zipPath);

        $zip = new ZipArchive;

        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new RuntimeException("No se pudo crear el ZIP [{$zipPath}].");
        }

        // template.json al root del ZIP.
        $zip->addFile($this->manifests->homePath($manifest->home).'/template.json', 'template.json');

        foreach ($manifest->types as $surface) {
            $surfaceDir = $this->manifests->basePath().'/'.$surface.'/templates/'.$manifest->slug;

            $this->addDirectory($zip, $surfaceDir, $surface.'/templates/'.$manifest->slug);
        }

        $zip->close();
    }

    /**
     * Añade un directorio completo de forma recursiva.
     */
    protected function addDirectory(ZipArchive $zip, string $dir, string $prefix): void
    {
        $files = File::allFiles($dir);

        foreach ($files as $file) {
            $relative = str_replace('\\', '/', $file->getRelativePathname());
            $localName = $prefix.'/'.$relative;

            if (str_contains($relative, 'node_modules') || $relative === '.DS_Store') {
                continue;
            }

            $zip->addFile($file->getPathname(), $localName);
        }
    }
}
