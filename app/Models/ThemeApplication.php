<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * Tema activo por superficie (proyecto global). Una fila por superficie;
 * ausencia de fila significa tema default.
 *
 * @property string $surface
 * @property string $slug
 * @property array<string, mixed>|null $settings
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['surface', 'slug', 'settings'])]
class ThemeApplication extends Model
{
    protected $casts = [
        'settings' => 'array',
    ];
}