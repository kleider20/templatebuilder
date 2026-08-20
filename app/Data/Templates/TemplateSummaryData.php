<?php

namespace App\Data\Templates;

use Spatie\LaravelData\Data;

/**
 * Ítem del catálogo de temas (página dashboard/plantillas).
 */
class TemplateSummaryData extends Data
{
    /**
     * @param  array<int, string>  $types
     * @param  array<string, mixed>  $settings
     * @param  array<string, mixed>|null  $theme
     * @param  array<string, mixed>  $pricing  {type: free|premium, price, currency}
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
        public array $pricing,
    ) {}
}
