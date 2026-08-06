<?php

namespace App\Services\Templates;

/**
 * DTO inmutable con la información de un rol de componente de una superficie.
 */
final readonly class TemplateComponentDto
{
    /**
     * @param  string  $role  rol del componente (template, layout, header…)
     * @param  string  $path  path relativo a resources/js/layouts/
     */
    public function __construct(
        public string $role,
        public string $path,
    ) {}

    /**
     * @param  array{role: string, path: string}  $data
     */
    public static function fromArray(array $data): self
    {
        return new self($data['role'], $data['path']);
    }
}
