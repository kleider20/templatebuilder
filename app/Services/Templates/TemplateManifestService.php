<?php

namespace App\Services\Templates;

use App\Data\Templates\TemplateManifestData;
use Illuminate\Support\Facades\File;
use RuntimeException;

/**
 * Descubre y valida los manifiestos de temas bajo resources/js/layouts/.
 */
class TemplateManifestService
{
    /**
     * Path absoluto del directorio base de authoring.
     */
    public function basePath(): string
    {
        return base_path((string) config('templates.base_path'));
    }

    /**
     * Path absoluto del home de un tema.
     */
    public function homePath(string $home): string
    {
        return $this->basePath().'/'.$home;
    }

    /**
     * Vocabulario rol=>superficie desde config.
     *
     * @return array<string, array<int, string>>
     */
    public function vocabulary(): array
    {
        return (array) config('templates.surfaces');
    }

    /**
     * Lista de DTOs de manifiesto válidos encontrados en el árbol.
     *
     * @return array<int, TemplateManifestData>
     */
    public function scan(): array
    {
        $manifests = [];

        foreach (File::directories($this->basePath()) as $themeDir) {
            $manifestPath = $themeDir.'/template.json';

            if (! is_file($manifestPath)) {
                continue;
            }

            try {
                $manifests[] = $this->loadFromPath($manifestPath);
            } catch (RuntimeException) {
                // Los manifiestos inválidos no entran en el catálogo.
            }
        }

        return $manifests;
    }

    /**
     * Carga y valida un manifiesto.
     *
     * @throws RuntimeException
     */
    public function loadFromPath(string $manifestPath): TemplateManifestData
    {
        $manifest = json_decode(File::get($manifestPath), true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new RuntimeException('Error de sintaxis JSON: '.json_last_error_msg());
        }

        return $this->validate($manifest, $manifestPath);
    }

    /**
     * Carga un manifiesto dado su slug.
     *
     * @throws RuntimeException si no existe o es inválido.
     */
    public function loadBySlug(string $slug): TemplateManifestData
    {
        $manifestPath = $this->findManifestPath($slug);

        if ($manifestPath === null) {
            throw new RuntimeException("Tema [{$slug}] no encontrado.");
        }

        return $this->loadFromPath($manifestPath);
    }

    /**
     * Localiza el path de template.json de un slug, sin validar el contenido
     * (para propagar errores reales de validación). Cada tema es una carpeta
     * hermana en layouts/ con su template.json en la raíz.
     */
    protected function findManifestPath(string $slug): ?string
    {
        $manifestPath = $this->basePath().'/'.$slug.'/template.json';

        return is_file($manifestPath) ? $manifestPath : null;
    }

    /**
     * Superficies soportadas.
     *
     * @return array<int, string>
     */
    public function surfaces(): array
    {
        return array_keys($this->vocabulary());
    }

    /**
     * Valida un manifiesto siguiendo la sección 3 del contrato.
     *
     * @param  array<string, mixed>  $manifest
     *
     * @throws RuntimeException
     */
    public function validate(array $manifest, ?string $manifestPath = null): TemplateManifestData
    {
        $name = $manifest['name'] ?? null;
        $slug = $manifest['slug'] ?? null;
        $version = $manifest['version'] ?? null;
        $types = $manifest['type'] ?? null;

        if (! is_string($name) || $name === '') {
            throw new RuntimeException('El manifest debe definir name.');
        }

        if (! is_string($slug) || $slug === '') {
            throw new RuntimeException('El manifest debe definir slug.');
        }

        if (! is_string($version) || ! $this->isSemver($version)) {
            throw new RuntimeException('El manifest debe definir version semver válida.');
        }

        if (! is_array($types) || $types === []) {
            throw new RuntimeException('type debe ser un array no vacío.');
        }

        $allowed = $this->surfaces();

        foreach ($types as $type) {
            if (! in_array($type, $allowed, true)) {
                throw new RuntimeException("Superficie [{$type}] no soportada.");
            }
        }

        $home = $slug;

        if (($manifest['home'] ?? null) !== $home) {
            throw new RuntimeException("home debe ser [{$home}].");
        }

        if (! is_dir($this->homePath($home))) {
            throw new RuntimeException("No existe el directorio del tema [{$home}].");
        }

        if (! isset($manifest['components']) || ! is_array($manifest['components'])) {
            throw new RuntimeException('El manifest debe definir components.');
        }

        foreach ($types as $type) {
            $roles = $manifest['components'][$type] ?? null;

            if (! is_array($roles)) {
                throw new RuntimeException("Faltan components para la superficie [{$type}].");
            }

            $vocabulary = $this->vocabulary()[$type];

            foreach ($vocabulary as $role) {
                $path = $roles[$role] ?? null;

                if (! is_string($path) || $path === '') {
                    throw new RuntimeException("Falta el rol [{$role}] en la superficie [{$type}].");
                }
            }

            foreach ($roles as $role => $path) {
                if (! in_array($role, $vocabulary, true)) {
                    throw new RuntimeException("Rol [{$role}] fuera del vocabulario en [{$type}].");
                }

                if (! is_string($path) || ! is_file($this->basePath().'/'.$path)) {
                    throw new RuntimeException("El path [{$path}] no existe en disco.");
                }
            }
        }

        $exports = $manifest['exports'] ?? null;

        if (! is_array($exports)) {
            throw new RuntimeException('El manifest debe definir exports.');
        }

        $expectedJs = "dist/{$slug}.js";
        $expectedCss = "dist/{$slug}.css";

        if (($exports['bundle'] ?? null) !== $expectedJs) {
            throw new RuntimeException("exports.bundle debe ser [{$expectedJs}].");
        }

        if (($exports['styles'] ?? null) !== $expectedCss) {
            throw new RuntimeException("exports.styles debe ser [{$expectedCss}].");
        }

        // Validación del bloque theme (opcional por compatibilidad).
        if (isset($manifest['theme'])) {
            $this->validateTheme($manifest['theme']);
        }

        return TemplateManifestData::fromManifest($manifest, $this->vocabulary());
    }

    /**
     * Valida el bloque theme según la sección 3.
     *
     *
     * @throws RuntimeException
     */
    protected function validateTheme(mixed $theme): void
    {
        if (! is_array($theme)) {
            throw new RuntimeException('theme debe ser un objeto.');
        }

        $roles = $theme['roles'] ?? null;
        $palettes = $theme['palettes'] ?? null;
        $default = $theme['default'] ?? null;

        if (! is_array($roles)) {
            throw new RuntimeException('theme.roles es obligatorio.');
        }

        $minRoles = ['primary', 'secondary', 'accent'];

        foreach ($minRoles as $role) {
            if (! in_array($role, $roles, true)) {
                throw new RuntimeException("theme.roles debe incluir [{$role}].");
            }
        }

        if (! is_array($palettes)) {
            throw new RuntimeException('theme.palettes es obligatorio.');
        }

        $minPalettes = (int) config('templates.min_palettes', 3);

        if (count($palettes) < $minPalettes) {
            throw new RuntimeException("theme.palettes debe tener al menos {$minPalettes} paletas.");
        }

        $ids = [];

        foreach ($palettes as $palette) {
            if (! is_array($palette) || ! isset($palette['id'])) {
                throw new RuntimeException('Cada paleta debe tener id.');
            }

            if (isset($ids[$palette['id']])) {
                throw new RuntimeException("Paleta duplicada [{$palette['id']}].");
            }

            $ids[$palette['id']] = true;
        }

        if (! is_string($default) || ! isset($ids[$default])) {
            throw new RuntimeException('theme.default debe referenciar una paleta existente.');
        }
    }

    /**
     * Comprueba que un string sea semver.
     */
    protected function isSemver(string $version): bool
    {
        return (bool) preg_match('/^\d+\.\d+\.\d+(-[0-9A-Za-z.-]+)?(\+[0-9A-Za-z.-]+)?$/', $version);
    }
}
