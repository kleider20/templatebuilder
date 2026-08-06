<?php

namespace App\Actions\Templates;

use App\Models\TemplateVersion;
use App\Services\Templates\TemplateExportService;
use App\Services\Templates\TemplateManifestService;
use RuntimeException;

class ExportTemplateAction
{
    public function __construct(
        protected TemplateExportService $export,
        protected TemplateManifestService $manifests,
    ) {}

    /**
     * Exporta un tema usando el slug del route param y auto-incrementando la
     * versión en caso de re-exportación (evita el error unique slug+version).
     *
     * @throws RuntimeException
     */
    public function handle(string $slug, int $userId): TemplateVersion
    {
        $manifest = $this->manifests->loadBySlug($slug);

        $version = $this->export->nextVersion($slug, $manifest->version);

        return $this->export->export($slug, $userId, $version);
    }
}
