<?php

namespace App\Data\Settings;

use Spatie\LaravelData\Data;

/**
 * Props de la página de perfil (settings/profile).
 */
class ProfileData extends Data
{
    public function __construct(
        public bool $mustVerifyEmail,
        public ?string $status,
    ) {}
}
