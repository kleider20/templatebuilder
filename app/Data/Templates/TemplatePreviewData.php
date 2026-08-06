<?php

namespace App\Data\Templates;

use Spatie\LaravelData\Data;

/**
 * Datos que consume el preview y el panel de personalización.
 */
class TemplatePreviewData extends Data
{
    /**
     * @param  array<string, mixed>  $manifest
     * @param  array<int, string>  $surfaces
     * @param  array<int, string>  $paletteIds
     */
    public function __construct(
        public array $manifest,
        public array $surfaces,
        public string $defaultPalette,
        public array $paletteIds,
        public ?string $palette,
    ) {}
}
