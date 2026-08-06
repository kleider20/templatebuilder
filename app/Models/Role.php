<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Spatie\Permission\Models\Role as SpatieRole;

/**
 * @property int $id
 * @property string $name
 * @property string $guard_name
 */
#[Fillable(['name', 'guard_name'])]
class Role extends SpatieRole
{
    public function metadata(): HasOne
    {
        return $this->hasOne(RoleMetadata::class, 'role_id');
    }
}
