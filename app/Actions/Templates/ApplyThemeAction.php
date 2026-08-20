<?php

namespace App\Actions\Templates;

use App\Data\Templates\TemplateManifestData;
use App\Models\ThemeApplication;
use App\Services\Templates\TemplateBuildService;
use App\Services\Templates\ThemeApplicationService;

class ApplyThemeAction
{
    public function __construct(
        protected TemplateBuildService $build,
        protected ThemeApplicationService $applications,
    ) {}

    /**
     * Aplica un tema a las superficies que soporta (manifest.types) de forma
     * global (una fila por superficie). Asegura dist/ compilado e invalida la
     * caché del tema activo.
     *
     * @return array<int, string> superficies aplicadas
     */
    public function handle(TemplateManifestData $manifest): array
    {
        $this->build->ensureBuilt($manifest);

        $applied = [];

        foreach ($manifest->types as $surface) {
            ThemeApplication::updateOrCreate(
                ['surface' => $surface],
                ['slug' => $manifest->slug],
            );

            $applied[] = $surface;
        }

        $this->applications->forgetCache();

        return $applied;
    }
}