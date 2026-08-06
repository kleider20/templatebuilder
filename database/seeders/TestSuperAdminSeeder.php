<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class TestSuperAdminSeeder extends Seeder
{
    public function run(): void
    {
        $superAdmin = User::updateOrCreate(
            ['email' => 'superadmin@templatebuilder.test'],
            [
                'name' => 'Super Admin de Pruebas',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
                'is_test_user' => true,
                'is_undeletable' => true,
            ]
        );

        $admin = User::updateOrCreate(
            ['email' => 'admin@templatebuilder.test'],
            [
                'name' => 'Admin de Pruebas',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
                'is_test_user' => true,
                'is_undeletable' => true,
            ]
        );

        $superAdmin->syncRoles(['super_admin']);
        $admin->syncRoles(['admin']);

        $allRoles = Role::where('guard_name', 'web')->pluck('name')->toArray();

        $this->command->info('✅ Super Admin de Pruebas sincronizado con todos los roles del sistema.');
        $this->command->info('✅ Admin de Pruebas creado con rol admin. Roles del sistema: '.implode(', ', $allRoles));
    }
}
