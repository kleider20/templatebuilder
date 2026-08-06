<?php

namespace App\Data\Templates;

use Spatie\LaravelData\Data;

/**
 * Archivo activo del editor con su contenido.
 */
class TemplateFileData extends Data
{
    public function __construct(
        public string $path,
        public string $content,
    ) {}
}
