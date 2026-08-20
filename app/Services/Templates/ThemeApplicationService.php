<?php

namespace App\Services\Templates;

use App\Models\ThemeApplication;
use RuntimeException;

/**
 * Tema activo por superficie (proyecto global). Lee las filas persistidas y
 * resuelve los manifiestos con caché corta invalidada al aplicar.
 */
class ThemeApplicationService
{
    public const CACHE_KEY = 'templates.theme_applications';

    public function __construct(
        protected TemplateManifestService $manifests,
    ) {}

    /**
     * Info por superficie del tema activo (o null si no hay / es inválido).
     *
     * @return array<string, array{slug: string, name: string, home: string, components: array<string, array<string, string>>, exports: array<string, string>}>
     */
    public function allActive(): array
    {
        return cache()->remember(self::CACHE_KEY, now()->addMinutes(5), function () {
            $active = [];

            foreach (ThemeApplication::all() as $application) {
                try {
                    $manifest = $this->manifests->loadBySlug($application->slug);
                } catch (RuntimeException) {
                    continue;
                }

                $active[$application->surface] = [
                    'slug' => $manifest->slug,
                    'name' => $manifest->name,
                    'home' => $manifest->home,
                    'components' => $manifest->components,
                    'exports' => $manifest->exports,
                ];
            }

            return $active;
        });
    }

    /**
     * Info del tema activo de una superficie, o null.
     *
     * @return array{slug: string, name: string, home: string, components: array<string, array<string, string>>, exports: array<string, string>}|null
     */
    public function activeFor(string $surface): ?array
    {
        return $this->allActive()[$surface] ?? null;
    }

    /**
     * Invalida la caché del tema activo.
     */
    public function forgetCache(): void
    {
        cache()->forget(self::CACHE_KEY);
    }
}