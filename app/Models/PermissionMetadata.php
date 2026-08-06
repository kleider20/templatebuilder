<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $permission_id
 * @property string $display_name
 * @property string|null $description
 * @property string $category
 * @property bool $is_critical
 */
#[Fillable(['permission_id', 'display_name', 'description', 'category', 'is_critical'])]
class PermissionMetadata extends Model
{
    public function permission(): BelongsTo
    {
        return $this->belongsTo(Permission::class, 'permission_id');
    }
}
