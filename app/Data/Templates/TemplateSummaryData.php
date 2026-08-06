<?php

namespace App\Data\Templates;

use Spatie\LaravelData\Data;

/**
 * Ítem del catálogo de temas (página templates/index).
 */
class TemplateSummaryData extends Data
{
    /**
     * @param  array<int, string>  $types
     * @param  array<string, mixed>  $settings
     * @param  array<string, mixed>|null  $theme
     */
    public function __construct(
        public string $name,
        public string $slug,
        public string $version,
        public string $author,
        public string $description,
        public array $types,
        public ?string $thumbnail,
        public string $home,
        public array $settings,
        public ?array $theme,
    ) {}
}
