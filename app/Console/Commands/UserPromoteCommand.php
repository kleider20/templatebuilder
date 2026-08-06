<?php

namespace App\Console\Commands;

use App\Models\Role;
use App\Models\User;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('user:promote {user} {role}')]
#[Description('Asigna un rol a un usuario (p.ej. admin o super_admin)')]
class UserPromoteCommand extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $identifier = $this->argument('user');
        $roleName = (string) $this->argument('role');

        $user = User::query()
            ->where('email', $identifier)
            ->orWhere('id', $identifier)
            ->first();

        if ($user === null) {
            $this->error("No se encontró el usuario [{$identifier}].");

            return self::FAILURE;
        }

        $role = Role::where('name', $roleName)->where('guard_name', 'web')->first();

        if ($role === null) {
            $this->error("El rol [{$roleName}] no existe.");

            return self::FAILURE;
        }

        $user->assignRole($role->name);

        $this->info("✅ Usuario [{$user->email}] ahora tiene el rol [{$role->name}].");

        return self::SUCCESS;
    }
}
