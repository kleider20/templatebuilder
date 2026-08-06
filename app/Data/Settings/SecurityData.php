<?php

namespace App\Data\Settings;

use Spatie\LaravelData\Data;

/**
 * Props de la página de seguridad (settings/security).
 */
class SecurityData extends Data
{
    /**
     * @param  array<int, array<string, mixed>>  $passkeys
     */
    public function __construct(
        public bool $canManageTwoFactor,
        public bool $canManagePasskeys,
        public array $passkeys,
        public string $passwordRules,
        public ?bool $twoFactorEnabled = null,
        public ?bool $requiresConfirmation = null,
    ) {}
}
