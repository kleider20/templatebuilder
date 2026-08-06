<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $role_id
 * @property string $display_name
 * @property string|null $description
 * @property string $color
 * @property string $icon_color
 * @property string $icon
 * @property bool $show_in_register
 * @property bool $is_protected
 * @property bool $can_edit_permissions
 * @property bool $is_fallback
 * @property string $level
 * @property int $users_count
 * @property int $permissions_count
 */
#[Fillable([
    'role_id', 'display_name', 'description', 'color', 'icon_color', 'icon',
    'show_in_register', 'is_protected', 'can_edit_permissions', 'is_fallback',
    'level', 'users_count', 'permissions_count',
])]
class RoleMetadata extends Model
{
    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'role_id');
    }
}
