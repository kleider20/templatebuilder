<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\PermissionMetadata;
use App\Models\Role;
use App\Models\RoleMetadata;
use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    private string $guard = 'web';

    private array $roleConfig = [
        'super_admin' => [
            'display_name' => 'Super Administrador',
            'description' => 'Acceso total al sistema. Rol protegido.',
            'color' => '#DC2626',
            'icon_color' => '#FFD700',
            'icon' => 'crown',
            'show_in_register' => false,
            'is_protected' => true,
            'can_edit_permissions' => true,
            'level' => 'Crítico',
        ],
        'admin' => [
            'display_name' => 'Administrador',
            'description' => 'Gestión administrativa del sistema.',
            'color' => '#2563EB',
            'icon_color' => '#FFFFFF',
            'icon' => 'user-shield',
            'show_in_register' => false,
            'is_protected' => true,
            'can_edit_permissions' => true,
            'level' => 'Alto',
        ],
        'editor' => [
            'display_name' => 'Editor',
            'description' => 'Edita y gestiona temas y contenidos.',
            'color' => '#7C3AED',
            'icon_color' => '#FFFFFF',
            'icon' => 'pencil-line',
            'show_in_register' => false,
            'is_protected' => false,
            'can_edit_permissions' => false,
            'level' => 'Medio',
        ],
        'user' => [
            'display_name' => 'Usuario',
            'description' => 'Rol por defecto al registrarse. Acceso básico.',
            'color' => '#059669',
            'icon_color' => '#FFFFFF',
            'icon' => 'user',
            'show_in_register' => true,
            'is_protected' => false,
            'can_edit_permissions' => false,
            'level' => 'Básico',
        ],
        'base' => [
            'display_name' => 'Rol Base',
            'description' => 'Rol por defecto cuando un usuario pierde su rol anterior. Acceso mínimo.',
            'color' => '#6B7280',
            'icon_color' => '#FFFFFF',
            'icon' => 'user',
            'show_in_register' => false,
            'is_protected' => true,
            'is_fallback' => true,
            'can_edit_permissions' => false,
            'level' => 'Básico',
        ],
    ];

    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $this->command?->info('🚀 Iniciando seed de Roles y Permisos (template builder)...');

        $this->createRoles();
        $this->assignRolePermissions();
        $this->assignSharedPermissions();
        $this->assignBaseRolePermissions();
        $this->assignGlobalPermissions();
        $this->syncSuperAdminPermissions();
        $this->updateMetadataCounters();

        $this->command?->info("🎉 Seed completado exitosamente para guard '{$this->guard}'.");
    }

    private function createRoles(): void
    {
        foreach ($this->roleConfig as $roleName => $config) {
            $role = Role::firstOrCreate([
                'name' => $roleName,
                'guard_name' => $this->guard,
            ]);

            RoleMetadata::updateOrCreate(
                ['role_id' => $role->id],
                [
                    'display_name' => $config['display_name'],
                    'description' => $config['description'] ?? null,
                    'color' => $config['color'],
                    'icon_color' => $config['icon_color'],
                    'icon' => $config['icon'],
                    'show_in_register' => $config['show_in_register'] ?? false,
                    'is_protected' => $config['is_protected'] ?? false,
                    'can_edit_permissions' => $config['can_edit_permissions'] ?? false,
                    'is_fallback' => $config['is_fallback'] ?? false,
                    'level' => $config['level'],
                    'users_count' => 0,
                    'permissions_count' => 0,
                ]
            );

            $this->command?->info("✅ Rol: {$config['display_name']} ({$config['level']})");
        }
    }

    /**
     * Crea permisos y los asigna a cada rol según normal/critical.
     */
    private function assignRolePermissions(): void
    {
        $rolePermissions = $this->getPermissionsData();

        foreach ($rolePermissions as $roleName => $perms) {
            $role = Role::findByName($roleName, $this->guard);

            $criticalNames = array_column($perms['critical'] ?? [], 'name');

            $combinedPerms = array_merge($perms['normal'] ?? [], $perms['critical'] ?? []);

            foreach ($combinedPerms as $permData) {
                $isCritical = in_array($permData['name'], $criticalNames, true);

                $perm = Permission::firstOrCreate([
                    'name' => $permData['name'],
                    'guard_name' => $this->guard,
                ]);

                PermissionMetadata::updateOrCreate(
                    ['permission_id' => $perm->id],
                    [
                        'display_name' => $permData['display_name'],
                        'description' => $permData['description'] ?? null,
                        'category' => $permData['category'],
                        'is_critical' => $isCritical,
                    ]
                );

                $role->givePermissionTo($perm);
            }

            $this->command?->info("✅ Permisos asignados a: {$roleName}");
        }
    }

    private function assignSharedPermissions(): void
    {
        foreach ($this->getSharedPermissionsData() as $permName => $permData) {
            $perm = Permission::firstOrCreate([
                'name' => $permName,
                'guard_name' => $this->guard,
            ]);

            PermissionMetadata::updateOrCreate(
                ['permission_id' => $perm->id],
                [
                    'display_name' => $permData['display_name'],
                    'description' => $permData['description'] ?? null,
                    'category' => $permData['category'],
                    'is_critical' => $permData['is_critical'] ?? false,
                ]
            );

            foreach ($permData['roles'] as $roleName) {
                Role::findByName($roleName, $this->guard)->givePermissionTo($perm);
            }
        }

        $this->command?->info('✅ Permisos compartidos asignados');
    }

    private function assignBaseRolePermissions(): void
    {
        $baseRole = Role::findByName('base', $this->guard);

        $basePerms = [
            ['name' => 'view_dashboard', 'display_name' => 'Ver Dashboard', 'description' => 'Acceso al panel principal', 'category' => 'Dashboard'],
        ];

        foreach ($basePerms as $permData) {
            $perm = Permission::firstOrCreate([
                'name' => $permData['name'],
                'guard_name' => $this->guard,
            ]);

            PermissionMetadata::updateOrCreate(
                ['permission_id' => $perm->id],
                [
                    'display_name' => $permData['display_name'],
                    'description' => $permData['description'] ?? null,
                    'category' => $permData['category'],
                    'is_critical' => false,
                ]
            );

            $baseRole->givePermissionTo($perm);
        }

        $this->command?->info('✅ Permisos base asignados a: Rol Base');
    }

    private function assignGlobalPermissions(): void
    {
        $superAdmin = Role::findByName('super_admin', $this->guard);

        foreach ($this->getGlobalPermissionsData() as $permData) {
            $perm = Permission::firstOrCreate([
                'name' => $permData['name'],
                'guard_name' => $this->guard,
            ]);

            PermissionMetadata::updateOrCreate(
                ['permission_id' => $perm->id],
                [
                    'display_name' => $permData['display_name'],
                    'description' => $permData['description'] ?? null,
                    'category' => $permData['category'],
                    'is_critical' => true,
                ]
            );

            $superAdmin->givePermissionTo($perm);
        }

        $this->command?->info('✅ Permisos globales asignados a super_admin');
    }

    private function syncSuperAdminPermissions(): void
    {
        $superAdmin = Role::findByName('super_admin', $this->guard);

        $allPerms = Permission::where('guard_name', $this->guard)->pluck('name')->toArray();

        $superAdmin->syncPermissions($allPerms);

        $this->command?->info('✅ Super Admin sincronizado con todos los permisos');
    }

    private function updateMetadataCounters(): void
    {
        $roles = Role::with('metadata')->where('guard_name', $this->guard)->get();

        foreach ($roles as $role) {
            if ($role->metadata) {
                $role->metadata->update([
                    'users_count' => User::role($role->name)->count(),
                    'permissions_count' => $role->permissions()->count(),
                ]);
            }
        }
    }

    /**
     * Permisos por rol (normal / critical). El rol admin recibe, por ahora,
     * TODOS los permisos existentes para probar el sistema.
     *
     * @return array<string, array{normal: array<int, array<string, string>>, critical: array<int, array<string, string>>}>
     */
    private function getPermissionsData(): array
    {
        $adminAll = [
            // Dashboard
            ['name' => 'view_dashboard', 'display_name' => 'Ver Dashboard', 'description' => 'Acceso al panel principal', 'category' => 'Dashboard'],
            // Templates
            ['name' => 'view_templates', 'display_name' => 'Ver Temas', 'description' => 'Ver la lista de temas del sistema', 'category' => 'Templates'],
            ['name' => 'view_versions', 'display_name' => 'Ver Versiones', 'description' => 'Ver el historial de versiones de un tema', 'category' => 'Templates'],
            ['name' => 'create_templates', 'display_name' => 'Crear Temas', 'description' => 'Crear nuevos temas', 'category' => 'Templates'],
            ['name' => 'edit_templates', 'display_name' => 'Editar Temas', 'description' => 'Editar temas propios', 'category' => 'Templates'],
            ['name' => 'export_templates', 'display_name' => 'Exportar Temas', 'description' => 'Exportar temas propios a ZIP', 'category' => 'Templates'],
            ['name' => 'delete_templates', 'display_name' => 'Eliminar Temas', 'description' => 'Eliminar temas propios', 'category' => 'Templates'],
            // Usuarios
            ['name' => 'view_users', 'display_name' => 'Ver Usuarios', 'description' => 'Ver la lista de usuarios', 'category' => 'Usuarios'],
            ['name' => 'create_users', 'display_name' => 'Crear Usuarios', 'description' => 'Crear nuevos usuarios', 'category' => 'Usuarios'],
            ['name' => 'edit_users', 'display_name' => 'Editar Usuarios', 'description' => 'Editar usuarios existentes', 'category' => 'Usuarios'],
            ['name' => 'delete_users', 'display_name' => 'Eliminar Usuarios', 'description' => 'Eliminar usuarios', 'category' => 'Usuarios'],
            // Roles / permisos
            ['name' => 'view_roles', 'display_name' => 'Ver Roles', 'description' => 'Ver roles y permisos', 'category' => 'Roles'],
            ['name' => 'assign_roles', 'display_name' => 'Asignar Roles', 'description' => 'Asignar roles a usuarios', 'category' => 'Roles'],
            ['name' => 'view_permissions', 'display_name' => 'Ver Permisos', 'description' => 'Ver la lista de permisos', 'category' => 'Permisos'],
            // Configuración
            ['name' => 'view_settings', 'display_name' => 'Ver Configuración', 'description' => 'Ver configuración del sistema', 'category' => 'Configuración'],
            ['name' => 'edit_settings', 'display_name' => 'Editar Configuración', 'description' => 'Modificar configuración del sistema', 'category' => 'Configuración'],
            // Sistema
            ['name' => 'view_logs', 'display_name' => 'Ver Logs', 'description' => 'Consultar logs del sistema', 'category' => 'Sistema'],
        ];

        $adminCritical = [
            ['name' => 'manage_templates', 'display_name' => 'Gestionar Temas', 'description' => 'Editar y exportar cualquier tema', 'category' => 'Templates'],
            ['name' => 'delete_all_templates', 'display_name' => 'Eliminar Cualquier Tema', 'description' => 'Eliminar temas de cualquier usuario', 'category' => 'Templates'],
            ['name' => 'view_all_templates', 'display_name' => 'Ver Todos los Temas', 'description' => 'Ver los temas creados por todos los usuarios', 'category' => 'Templates'],
            ['name' => 'manage_users', 'display_name' => 'Gestionar Usuarios', 'description' => 'Control total sobre usuarios', 'category' => 'Usuarios'],
            ['name' => 'manage_roles', 'display_name' => 'Gestionar Roles', 'description' => 'Crear, editar y eliminar roles', 'category' => 'Roles'],
            ['name' => 'manage_permissions', 'display_name' => 'Gestionar Permisos', 'description' => 'Ver y gestionar permisos del sistema', 'category' => 'Permisos'],
            ['name' => 'manage_roles_permission', 'display_name' => 'Gestionar Roles y Permisos', 'description' => 'Acceso a la sección Roles y Permisos', 'category' => 'Roles'],
            ['name' => 'manage_database', 'display_name' => 'Gestión de BD', 'description' => 'Mantenimiento de base de datos', 'category' => 'Sistema'],
        ];

        $editorNormal = [
            ['name' => 'view_dashboard', 'display_name' => 'Ver Dashboard', 'description' => 'Acceso al panel principal', 'category' => 'Dashboard'],
            ['name' => 'view_templates', 'display_name' => 'Ver Temas', 'description' => 'Ver la lista de temas del sistema', 'category' => 'Templates'],
            ['name' => 'view_versions', 'display_name' => 'Ver Versiones', 'description' => 'Ver el historial de versiones de un tema', 'category' => 'Templates'],
            ['name' => 'view_users', 'display_name' => 'Ver Usuarios', 'description' => 'Ver la lista de usuarios', 'category' => 'Usuarios'],
        ];

        $userNormal = [
            ['name' => 'view_dashboard', 'display_name' => 'Ver Dashboard', 'description' => 'Acceso al panel principal', 'category' => 'Dashboard'],
            ['name' => 'view_templates', 'display_name' => 'Ver Temas', 'description' => 'Ver la lista de temas del sistema', 'category' => 'Templates'],
            ['name' => 'view_versions', 'display_name' => 'Ver Versiones', 'description' => 'Ver el historial de versiones de un tema', 'category' => 'Templates'],
        ];

        return [
            'admin' => [
                'normal' => $adminAll,
                'critical' => $adminCritical,
            ],
            'editor' => [
                'normal' => $editorNormal,
                'critical' => [],
            ],
            'user' => [
                'normal' => $userNormal,
                'critical' => [],
            ],
        ];
    }

    private function getGlobalPermissionsData(): array
    {
        return [
            ['name' => 'manage_global_config', 'display_name' => 'Configuración Global', 'description' => 'Ajustar parámetros del sistema', 'category' => 'Sistema'],
            ['name' => 'manage_role_security', 'display_name' => 'Gestionar Seguridad del Rol', 'description' => 'Controlar edición de permisos y visibilidad de roles', 'category' => 'Sistema'],
        ];
    }

    private function getSharedPermissionsData(): array
    {
        return [
            'view_notifications' => [
                'display_name' => 'Ver Notificaciones',
                'description' => 'Recibir y ver notificaciones del sistema',
                'category' => 'General',
                'roles' => ['super_admin', 'admin', 'editor'],
            ],
            'edit_own_profile' => [
                'display_name' => 'Editar Perfil Propio',
                'description' => 'Modificar su propio perfil',
                'category' => 'General',
                'roles' => ['super_admin', 'admin', 'editor', 'user'],
            ],
            'view_profile' => [
                'display_name' => 'Ver Perfil',
                'description' => 'Ver el perfil de otros usuarios',
                'category' => 'Usuarios',
                'roles' => ['super_admin'],
            ],
            'view_user_stats' => [
                'display_name' => 'Ver Estadísticas de Usuarios',
                'description' => 'Ver stat cards de usuarios en el dashboard',
                'category' => 'Dashboard Widgets',
                'roles' => ['super_admin', 'admin'],
            ],
            'view_template_stats' => [
                'display_name' => 'Ver Estadísticas de Temas',
                'description' => 'Ver stat cards de temas en el dashboard',
                'category' => 'Dashboard Widgets',
                'roles' => ['super_admin', 'admin'],
            ],
            'view_system_health' => [
                'display_name' => 'Ver Estado del Sistema',
                'description' => 'Ver estado de servicios del sistema',
                'category' => 'Dashboard Widgets',
                'roles' => ['super_admin'],
            ],
        ];
    }
}
