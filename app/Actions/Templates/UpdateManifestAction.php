<?php

namespace App\Actions\Templates;

use App\Data\Templates\TemplateManifestData;
use App\Services\Templates\TemplateManifestService;
use Illuminate\Support\Facades\File;
use RuntimeException;

class UpdateManifestAction
{
    public function __construct(
        protected TemplateManifestService $manifests,
    ) {}

    /**
     * Reescribe settings/theme/pricing del template.json validando sobre una copia.
     *
     * @param  array<string, mixed>|null  $settings
     * @param  array<string, mixed>|null  $theme
     * @param  array<string, mixed>|null  $pricing
     * @return array<string, mixed>
     *
     * @throws RuntimeException
     */
    public function handle(TemplateManifestData $manifest, ?array $settings = null, ?array $theme = null, ?array $pricing = null): array
    {
        $current = $manifest->toManifestArray();

        $next = $current;

        if ($settings !== null) {
            $next['settings'] = $settings;
        }

        if ($theme !== null) {
            $next['theme'] = $theme;
        }

        if ($pricing !== null) {
            $next['pricing'] = $pricing;
        }

        $this->manifests->validate($next);

        $manifestPath = $this->manifests->homePath($manifest->home).'/template.json';

        File::put($manifestPath, json_encode($next, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        return $next;
    }
}
