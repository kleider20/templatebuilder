<?php

namespace App\Actions\Templates;

use App\Data\Templates\TemplateSummaryData;
use App\Models\TemplateVersion;
use App\Services\Templates\TemplateManifestService;

/**
 * Cataloga los temas del marketplace público: lista todos los manifiestos
 * válidos (sin filtro de propietario) con su pricing, thumbnail y disponibilidad
 * de descarga (último ZIP exportado presente en output/).
 */
class MarketplaceTemplatesAction
{
    public function __construct(
        protected TemplateManifestService $manifests,
    ) {}

    /**
     * @return array<int, array<string, mixed>>
     */
    public function handle(): array
    {
        return collect($this->manifests->scan())
            ->sortByDesc(fn ($manifest) => $manifest->name)
            ->map(function ($manifest) {
                $latest = TemplateVersion::query()
                    ->where('slug', $manifest->slug)
                    ->latest('created_at')
                    ->first();

                $downloadable = $latest !== null && is_file(base_path($latest->zip_path));

                return [
                    ...TemplateSummaryData::from([
                        'name' => $manifest->name,
                        'slug' => $manifest->slug,
                        'version' => $manifest->version,
                        'author' => $manifest->author,
                        'description' => $manifest->description,
                        'types' => $manifest->types,
                        'thumbnail' => $this->thumbnailUrl($manifest->home, $manifest->thumbnail),
                        'home' => $manifest->home,
                        'settings' => $manifest->settings,
                        'theme' => $manifest->theme,
                        'pricing' => $manifest->pricing,
                    ])->toArray(),
                    'downloadable' => $downloadable,
                    'download_url' => $downloadable ? route('marketplace.download', $manifest->slug) : null,
                ];
            })
            ->values()
            ->all();
    }

    /**
     * URL canónica del thumbnail o null si no existe el archivo.
     */
    protected function thumbnailUrl(string $home, ?string $thumbnail): ?string
    {
        if ($thumbnail === null) {
            return null;
        }

        $slug = basename($home);
        $file = pathinfo($thumbnail, PATHINFO_BASENAME);

        $path = $this->manifests->homePath($home).'/thumbnails/'.$file;

        if (! is_file($path)) {
            return null;
        }

        return url(config('templates.url_prefix').'/'.$slug.'/thumbnails/'.$file);
    }
}
