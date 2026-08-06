<?php

namespace App\Data\Templates;

use Spatie\LaravelData\Data;

/**
 * Nodo del árbol multi-raíz del editor de temas.
 */
class TemplateTreeNodeData extends Data
{
    public function __construct(
        public string $name,
        public string $type,
        public string $path,
    ) {}
}
