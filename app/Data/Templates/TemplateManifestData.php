<?php

namespace App\Data\Templates;

use Spatie\LaravelData\Data;

/**
 * Data object (spatie/laravel-data) de un manifiesto de tema (template.json) validado.
 */
class TemplateManifestData extends Data
{
    /**
     * @param  array<int, string>  $types
     * @param  string  $home  home del tema relativo a layouts/ (ej: aurora)
     * @param  array<string, array<string, string>>  $components  superficie => [rol => path]
     * @param  array<string, mixed>|null  $theme
     * @param  array<string, mixed>  $settings
     * @param  array<string, mixed>  $pricing  {type: free|premium, price: ?float, currency}
     * @param  array<string, mixed>  $dependencies
     * @param  array<string, mixed>  $requires
     * @param  array<string, string>  $exports
     */
    public function __construct(
        public string $name,
        public string $slug,
        public string $version,
        public array $types,
        public string $author,
        public string $description,
        public ?string $thumbnail,
        public string $home,
        public array $components,
        public ?array $theme,
        public array $settings,
        public array $pricing,
        public array $dependencies,
        public array $requires,
        public array $exports,
    ) {}

    /**
     * Construye el Data object desde el array crudo del manifiesto.
     *
     * @param  array<string, mixed>  $m
     * @param  array<string, array<int, string>>  $vocabulary
     */
    public static function fromManifest(array $m, array $vocabulary): self
    {
        $components = [];

        foreach ($m['components'] ?? [] as $surface => $roles) {
            if (! is_array($roles)) {
                continue;
            }

            foreach ($roles as $role => $path) {
                if (in_array($role, $vocabulary[$surface] ?? [], true) && is_string($path)) {
                    $components[$surface][$role] = $path;
                }
            }
        }

        return new self(
            name: (string) ($m['name'] ?? ''),
            slug: (string) ($m['slug'] ?? ''),
            version: (string) ($m['version'] ?? ''),
            types: array_values(array_map(strval(...), $m['type'] ?? [])),
            author: (string) ($m['author'] ?? ''),
            description: (string) ($m['description'] ?? ''),
            thumbnail: isset($m['thumbnail']) ? (string) $m['thumbnail'] : null,
            home: (string) ($m['home'] ?? ''),
            components: $components,
            theme: isset($m['theme']) && is_array($m['theme']) ? $m['theme'] : null,
            settings: isset($m['settings']) && is_array($m['settings']) ? $m['settings'] : [],
            pricing: isset($m['pricing']) && is_array($m['pricing'])
                ? self::normalizePricing($m['pricing'])
                : ['type' => 'free', 'price' => null, 'currency' => 'USD'],
            dependencies: isset($m['dependencies']) && is_array($m['dependencies']) ? $m['dependencies'] : [],
            requires: isset($m['requires']) && is_array($m['requires']) ? $m['requires'] : [],
            exports: isset($m['exports']) && is_array($m['exports']) ? $m['exports'] : [],
        );
    }

    /**
     * Reconstruye el array del manifiesto tal como se escribe en template.json.
     *
     * @return array<string, mixed>
     */
    public function toManifestArray(): array
    {
        return [
            'name' => $this->name,
            'slug' => $this->slug,
            'type' => $this->types,
            'version' => $this->version,
            'author' => $this->author,
            'description' => $this->description,
            'thumbnail' => $this->thumbnail,
            'home' => $this->home,
            'components' => $this->components,
            'theme' => $this->theme,
            'settings' => $this->settings,
            'pricing' => $this->pricing,
            'dependencies' => $this->dependencies,
            'requires' => $this->requires,
            'exports' => $this->exports,
        ];
    }

    /**
     * Normaliza un bloque pricing a su forma canónica.
     *
     * @param  array<string, mixed>  $pricing
     * @return array{type: string, price: int|float|null, currency: string}
     */
    protected static function normalizePricing(array $pricing): array
    {
        $type = ($pricing['type'] ?? 'free') === 'premium' ? 'premium' : 'free';

        $price = isset($pricing['price']) && is_numeric($pricing['price'])
            ? (float) $pricing['price']
            : null;

        if ($type === 'free') {
            $price = null;
        }

        return [
            'type' => $type,
            'price' => $price,
            'currency' => is_string($pricing['currency'] ?? null) ? strtoupper($pricing['currency']) : 'USD',
        ];
    }
}
