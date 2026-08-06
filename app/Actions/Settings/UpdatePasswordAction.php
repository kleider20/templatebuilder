<?php

namespace App\Actions\Settings;

use App\Models\User;

class UpdatePasswordAction
{
    /**
     * Actualiza la contraseña del usuario.
     */
    public function handle(User $user, string $password): void
    {
        $user->update([
            'password' => $password,
        ]);
    }
}
