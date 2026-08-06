<?php

namespace App\Actions\Settings;

use App\Models\User;

class UpdateProfileAction
{
    /**
     * Actualiza los datos de perfil y desmarca el email verificado si cambió.
     *
     * @param  array<string, mixed>  $data
     */
    public function handle(User $user, array $data): void
    {
        $user->fill($data);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();
    }
}
