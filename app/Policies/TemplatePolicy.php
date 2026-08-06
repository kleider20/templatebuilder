<?php

namespace App\Policies;

use App\Models\Template;
use App\Models\User;

/**
 * Autorización sobre temas (templates).
 *
 * Reglas:
 * - Crear  -> permiso `create_templates` (solo super_admin/admin de momento).
 * - Editar/exportar -> dueño con su permiso, o "manage_templates" (cualquiera).
 * - Eliminar -> dueño con "delete_templates", o "delete_all_templates"/"manage_templates".
 * - Ver todos -> "view_all_templates" (o rol super_admin).
 */
class TemplatePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('view_templates');
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('create_templates');
    }

    public function view(User $user, Template $template): bool
    {
        if ($user->hasPermissionTo('view_all_templates')) {
            return true;
        }

        return $template->user_id === $user->id && $user->hasPermissionTo('view_templates');
    }

    public function update(User $user, Template $template): bool
    {
        if ($user->hasPermissionTo('manage_templates')) {
            return true;
        }

        return $template->user_id === $user->id && $user->hasPermissionTo('edit_templates');
    }

    public function export(User $user, Template $template): bool
    {
        if ($user->hasPermissionTo('manage_templates')) {
            return true;
        }

        return $template->user_id === $user->id && $user->hasPermissionTo('export_templates');
    }

    public function delete(User $user, Template $template): bool
    {
        if ($user->hasPermissionTo('manage_templates') || $user->hasPermissionTo('delete_all_templates')) {
            return true;
        }

        return $template->user_id === $user->id && $user->hasPermissionTo('delete_templates');
    }

    public function viewAll(User $user): bool
    {
        return $user->hasPermissionTo('view_all_templates');
    }
}
