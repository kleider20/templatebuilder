<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Spatie\Permission\Models\Permission as SpatiePermission;

/**
 * @property int $id
 * @property string $name
 * @property string $guard_name
 */
#[Fillable(['name', 'guard_name'])]
class Permission extends SpatiePermission
{
    public function metadata(): HasOne
    {
        return $this->hasOne(PermissionMetadata::class, 'permission_id');
    }
}
