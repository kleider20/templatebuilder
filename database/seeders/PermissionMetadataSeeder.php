<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\PermissionMetadata;
use Illuminate\Database\Seeder;

class PermissionMetadataSeeder extends Seeder
{
    public function run(): void
    {
        if (app()->environment('local')) {
            PermissionMetadata::truncate();
        }

        $permissionMetadata = [
            ['name' => 'view_dashboard', 'display_name' => 'Ver Dashboard', 'description' => 'Permite acceder al panel principal', 'category' => 'Dashboard', 'is_critical' => false],
            ['name' => 'view_templates', 'display_name' => 'Ver Temas', 'description' => 'Permite ver la lista de temas del sistema', 'category' => 'Templates', 'is_critical' => false],
            ['name' => 'view_versions', 'display_name' => 'Ver Versiones', 'description' => 'Permite ver el historial de versiones de un tema', 'category' => 'Templates', 'is_critical' => false],
            ['name' => 'create_templates', 'display_name' => 'Crear Temas', 'description' => 'Permite crear nuevos temas', 'category' => 'Templates', 'is_critical' => false],
            ['name' => 'edit_templates', 'display_name' => 'Editar Temas', 'description' => 'Permite editar temas propios', 'category' => 'Templates', 'is_critical' => false],
            ['name' => 'export_templates', 'display_name' => 'Exportar Temas', 'description' => 'Permite exportar temas propios a ZIP', 'category' => 'Templates', 'is_critical' => false],
            ['name' => 'delete_templates', 'display_name' => 'Eliminar Temas', 'description' => 'Permite eliminar temas propios', 'category' => 'Templates', 'is_critical' => false],
            ['name' => 'manage_templates', 'display_name' => 'Gestionar Temas', 'description' => 'Permite editar y exportar cualquier tema', 'category' => 'Templates', 'is_critical' => true],
            ['name' => 'delete_all_templates', 'display_name' => 'Eliminar Cualquier Tema', 'description' => 'Permite eliminar temas de cualquier usuario', 'category' => 'Templates', 'is_critical' => true],
            ['name' => 'view_all_templates', 'display_name' => 'Ver Todos los Temas', 'description' => 'Permite ver los temas creados por todos los usuarios', 'category' => 'Templates', 'is_critical' => true],
            ['name' => 'view_users', 'display_name' => 'Ver Usuarios', 'description' => 'Permite ver la lista de usuarios', 'category' => 'Usuarios', 'is_critical' => false],
            ['name' => 'create_users', 'display_name' => 'Crear Usuarios', 'description' => 'Permite crear nuevos usuarios', 'category' => 'Usuarios', 'is_critical' => false],
            ['name' => 'edit_users', 'display_name' => 'Editar Usuarios', 'description' => 'Permite editar usuarios existentes', 'category' => 'Usuarios', 'is_critical' => false],
            ['name' => 'delete_users', 'display_name' => 'Eliminar Usuarios', 'description' => 'Permite eliminar usuarios', 'category' => 'Usuarios', 'is_critical' => false],
            ['name' => 'manage_users', 'display_name' => 'Gestionar Usuarios', 'description' => 'Control total sobre usuarios', 'category' => 'Usuarios', 'is_critical' => true],
            ['name' => 'view_roles', 'display_name' => 'Ver Roles', 'description' => 'Permite ver roles y permisos', 'category' => 'Roles', 'is_critical' => false],
            ['name' => 'assign_roles', 'display_name' => 'Asignar Roles', 'description' => 'Permite asignar roles a usuarios', 'category' => 'Roles', 'is_critical' => false],
            ['name' => 'manage_roles', 'display_name' => 'Gestionar Roles', 'description' => 'Permite crear, editar y eliminar roles', 'category' => 'Roles', 'is_critical' => true],
            ['name' => 'manage_roles_permission', 'display_name' => 'Gestionar Roles y Permisos', 'description' => 'Acceso a la sección Roles y Permisos', 'category' => 'Roles', 'is_critical' => true],
            ['name' => 'view_permissions', 'display_name' => 'Ver Permisos', 'description' => 'Permite ver la lista de permisos', 'category' => 'Permisos', 'is_critical' => false],
            ['name' => 'manage_permissions', 'display_name' => 'Gestionar Permisos', 'description' => 'Permite crear, editar y eliminar permisos', 'category' => 'Permisos', 'is_critical' => true],
            ['name' => 'view_settings', 'display_name' => 'Ver Configuración', 'description' => 'Permite ver configuración del sistema', 'category' => 'Configuración', 'is_critical' => false],
            ['name' => 'edit_settings', 'display_name' => 'Editar Configuración', 'description' => 'Permite modificar configuración del sistema', 'category' => 'Configuración', 'is_critical' => false],
            ['name' => 'view_logs', 'display_name' => 'Ver Logs', 'description' => 'Permite consultar logs del sistema', 'category' => 'Sistema', 'is_critical' => false],
            ['name' => 'manage_database', 'display_name' => 'Gestión de BD', 'description' => 'Mantenimiento de base de datos', 'category' => 'Sistema', 'is_critical' => true],
            ['name' => 'manage_global_config', 'display_name' => 'Configuración Global', 'description' => 'Ajustar parámetros del sistema', 'category' => 'Sistema', 'is_critical' => true],
            ['name' => 'manage_role_security', 'display_name' => 'Gestionar Seguridad del Rol', 'description' => 'Controlar edición de permisos y visibilidad de roles', 'category' => 'Sistema', 'is_critical' => true],
            ['name' => 'view_notifications', 'display_name' => 'Ver Notificaciones', 'description' => 'Recibir y ver notificaciones del sistema', 'category' => 'General', 'is_critical' => false],
            ['name' => 'edit_own_profile', 'display_name' => 'Editar Perfil Propio', 'description' => 'Modificar su propio perfil', 'category' => 'General', 'is_critical' => false],
            ['name' => 'view_profile', 'display_name' => 'Ver Perfil', 'description' => 'Ver el perfil de otros usuarios', 'category' => 'Usuarios', 'is_critical' => false],
            ['name' => 'view_user_stats', 'display_name' => 'Ver Estadísticas de Usuarios', 'description' => 'Ver stat cards de usuarios en el dashboard', 'category' => 'Dashboard Widgets', 'is_critical' => false],
            ['name' => 'view_template_stats', 'display_name' => 'Ver Estadísticas de Temas', 'description' => 'Ver stat cards de temas en el dashboard', 'category' => 'Dashboard Widgets', 'is_critical' => false],
            ['name' => 'view_system_health', 'display_name' => 'Ver Estado del Sistema', 'description' => 'Ver estado de servicios del sistema', 'category' => 'Dashboard Widgets', 'is_critical' => false],
        ];

        foreach ($permissionMetadata as $metadata) {
            $permission = Permission::where('name', $metadata['name'])->first();

            if ($permission) {
                PermissionMetadata::updateOrCreate(
                    ['permission_id' => $permission->id],
                    [
                        'display_name' => $metadata['display_name'],
                        'description' => $metadata['description'],
                        'category' => $metadata['category'],
                        'is_critical' => $metadata['is_critical'],
                    ]
                );
            }
        }

        $this->command->info('✅ Metadatos de permisos creados exitosamente.');
    }
}
