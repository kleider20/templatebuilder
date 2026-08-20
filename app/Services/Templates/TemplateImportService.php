<?php

namespace App\Services\Templates;

use App\Data\Templates\TemplateManifestData;
use App\Models\Template;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use RuntimeException;
use ZipArchive;

/**
 * Importa un tema al proyecto: desde un ZIP de export (lo extrae, valida el
 * manifiesto contra una base temporal y copia la carpeta del tema a
 * resources/js/layouts/) o desde un slug ya extraído. Registra la propiedad en
 * la tabla templates (si se indica usuario) y recompila dist/ si falta.
 */
class TemplateImportService
{
    public function __construct(
        protected TemplateManifestService $manifests,
        protected TemplateBuildService $build,
    ) {}

    /**
     * Importa un tema desde un ZIP de exportación.
     *
     * @throws RuntimeException
     */
    public function importFromZip(string $zipPath, bool $force = false, ?int $userId = null): TemplateManifestData
    {
        if (! is_file($zipPath)) {
            throw new RuntimeException("El archivo [{$zipPath}] no existe.");
        }

        $tempBase = storage_path('app/template-imports');

        if (! is_dir($tempBase)) {
            File::makeDirectory($tempBase, 0755, true);
        }

        $temp = $tempBase.'/'.Str::uuid()->toString();
        File::makeDirectory($temp, 0755, true);

        try {
            $zip = new ZipArchive;

            if ($zip->open($zipPath) !== true) {
                throw new RuntimeException("No se pudo abrir el ZIP [{$zipPath}].");
            }

            $zip->extractTo($temp);
            $zip->close();

            $manifestPath = $temp.'/template.json';

            if (! is_file($manifestPath)) {
                throw new RuntimeException('El ZIP no contiene template.json en su raíz.');
            }

            $originalBase = config('templates.base_path');
            config(['templates.base_path' => 'storage/app/template-imports/'.basename($temp)]);

            try {
                $manifest = $this->manifests->loadFromPath($manifestPath);
            } finally {
                config(['templates.base_path' => $originalBase]);
            }

            $this->install($manifest, $force, $userId, $temp.'/'.$manifest->home);
        } finally {
            File::deleteDirectory($temp);
        }

        return $manifest;
    }

    /**
     * Importa un tema que ya está extraído en resources/js/layouts/{slug}.
     *
     * @throws RuntimeException
     */
    public function importFromSlug(string $slug, ?int $userId = null): TemplateManifestData
    {
        $manifest = $this->manifests->loadBySlug($slug);

        $this->install($manifest, false, $userId, null);

        return $manifest;
    }

    /**
     * Copia el tema al home real, registra la propiedad y asegura dist/.
     *
     * @throws RuntimeException
     */
    protected function install(TemplateManifestData $manifest, bool $force, ?int $userId, ?string $sourceDir): void
    {
        $home = $manifest->home;
        $dst = $this->manifests->homePath($home);

        if ($sourceDir !== null) {
            if (is_dir($dst) && ! $force) {
                throw new RuntimeException("El tema [{$home}] ya existe en resources/js/layouts/{$home}. Usá --force para sobrescribirlo.");
            }

            File::deleteDirectory($dst);
            File::copyDirectory($sourceDir, $dst);
        }

        if ($userId !== null) {
            Template::updateOrCreate(
                ['slug' => $manifest->slug],
                ['name' => $manifest->name, 'user_id' => $userId],
            );
        }

        $this->build->ensureBuilt($manifest);
    }
}
