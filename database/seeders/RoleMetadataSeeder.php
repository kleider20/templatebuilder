<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Role;
use App\Models\RoleMetadata;
use Illuminate\Database\Seeder;

class RoleMetadataSeeder extends Seeder
{
    public function run(): void
    {
        if (app()->environment('local')) {
            RoleMetadata::truncate();
        }

        $roleMetadata = [
            [
                'role_name' => 'super_admin',
                'display_name' => 'Super Administrador',
                'description' => 'Tiene acceso completo a todas las funcionalidades del sistema',
                'color' => '#DC2626',
                'icon' => 'crown',
                'level' => 'Crítico',
                'show_in_register' => false,
                'is_protected' => true,
                'can_edit_permissions' => true,
            ],
            [
                'role_name' => 'admin',
                'display_name' => 'Administrador',
                'description' => 'Gestor del sistema con acceso a la mayoría de funcionalidades',
                'color' => '#2563EB',
                'icon' => 'user-shield',
                'level' => 'Alto',
                'show_in_register' => false,
                'is_protected' => true,
                'can_edit_permissions' => true,
            ],
            [
                'role_name' => 'editor',
                'display_name' => 'Editor',
                'description' => 'Edita y gestiona temas y contenido del sistema',
                'color' => '#7C3AED',
                'icon' => 'pencil-line',
                'level' => 'Medio',
                'show_in_register' => false,
                'is_protected' => false,
                'can_edit_permissions' => false,
            ],
            [
                'role_name' => 'user',
                'display_name' => 'Usuario',
                'description' => 'Rol por defecto al registrarse. Acceso básico al sistema',
                'color' => '#059669',
                'icon' => 'user',
                'level' => 'Básico',
                'show_in_register' => true,
                'is_protected' => false,
                'can_edit_permissions' => false,
            ],
            [
                'role_name' => 'base',
                'display_name' => 'Rol Base',
                'description' => 'Rol por defecto cuando un usuario pierde su rol anterior. Acceso mínimo',
                'color' => '#6B7280',
                'icon' => 'user',
                'level' => 'Básico',
                'show_in_register' => false,
                'is_protected' => true,
                'can_edit_permissions' => false,
                'is_fallback' => true,
            ],
        ];

        foreach ($roleMetadata as $metadata) {
            $role = Role::where('name', $metadata['role_name'])->first();

            if ($role) {
                RoleMetadata::updateOrCreate(
                    ['role_id' => $role->id],
                    [
                        'display_name' => $metadata['display_name'],
                        'description' => $metadata['description'],
                        'color' => $metadata['color'],
                        'icon_color' => '#FFFFFF',
                        'icon' => $metadata['icon'],
                        'show_in_register' => $metadata['show_in_register'],
                        'is_protected' => $metadata['is_protected'],
                        'can_edit_permissions' => $metadata['can_edit_permissions'],
                        'is_fallback' => $metadata['is_fallback'] ?? false,
                        'level' => $metadata['level'],
                        'users_count' => 0,
                        'permissions_count' => $role->permissions()->count(),
                    ]
                );
            }
        }

        $this->command->info('✅ Metadatos de roles creados exitosamente.');
    }
}
