<?php

namespace App\Data\Settings;

use Spatie\LaravelData\Data;

/**
 * Props de la página de apariencia (settings/appearance).
 */
class AppearanceData extends Data
{
    public function __construct(
        public string $appearance,
    ) {}
}
