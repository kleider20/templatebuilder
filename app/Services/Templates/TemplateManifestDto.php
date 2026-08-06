<?php

namespace App\Services\Templates;

/**
 * DTO inmutable de un manifiesto de tema (template.json) validado.
 */
final readonly class TemplateManifestDto
{
    /**
     * @param  array<int, string>  $types  superficies declaradas
     * @param  string  $home  home del tema relativo a layouts/ (ej: landing/templates/aurora)
     * @param  array<string, array<string, TemplateComponentDto>>  $components  superficie => [rol => DTO]
     * @param  array<string, mixed>|null  $theme
     * @param  array<string, mixed>  $settings
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
        public array $dependencies,
        public array $requires,
        public array $exports,
    ) {}

    /**
     * @param  array<string, mixed>  $m
     * @param  array<string, array<int, string>>  $vocabulary
     */
    public static function fromArray(array $m, array $vocabulary): self
    {
        $components = [];
        foreach ($m['components'] ?? [] as $surface => $roles) {
            if (! is_array($roles)) {
                continue;
            }
            foreach ($roles as $role => $path) {
                if (in_array($role, $vocabulary[$surface] ?? [], true) && is_string($path)) {
                    $components[$surface][$role] = new TemplateComponentDto($role, $path);
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
        $components = [];

        foreach ($this->components as $surface => $roles) {
            foreach ($roles as $role => $dto) {
                $components[$surface][$role] = $dto->path;
            }
        }

        return [
            'name' => $this->name,
            'slug' => $this->slug,
            'type' => $this->types,
            'version' => $this->version,
            'author' => $this->author,
            'description' => $this->description,
            'thumbnail' => $this->thumbnail,
            'home' => $this->home,
            'components' => $components,
            'theme' => $this->theme,
            'settings' => $this->settings,
            'dependencies' => $this->dependencies,
            'requires' => $this->requires,
            'exports' => $this->exports,
        ];
    }
}
