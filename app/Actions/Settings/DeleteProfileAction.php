<?php

namespace App\Actions\Settings;

use App\Models\User;

class DeleteProfileAction
{
    /**
     * Elimina el perfil del usuario (los tests de super admin son inborrables).
     *
     * @throws \RuntimeException
     */
    public function handle(User $user): void
    {
        if ($user->is_undeletable) {
            throw new \RuntimeException('Este usuario no puede ser eliminado.');
        }

        $user->setRememberToken(null);

        $user->delete();
    }
}
