<?php

use App\Models\User;
use App\Services\Templates\TemplateScaffoldService;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Support\Facades\File;

beforeEach(function () {
    config(['templates.base_path' => 'storage/app/template-tests']);
    File::deleteDirectory(storage_path('app/template-tests'));
    File::makeDirectory(storage_path('app/template-tests'), 0755, true);
});

afterEach(function () {
    File::deleteDirectory(storage_path('app/template-tests'));
});

function dashboardPlantillasTheme(string $slug = 'aurora'): string
{
    return app(TemplateScaffoldService::class)->make(slug: $slug, types: ['landing', 'dashboard'], name: 'Aurora');
}

test('dashboard.plantillas exige autenticación (redirect a login)', function () {
    dashboardPlantillasTheme();

    $this->get(route('dashboard.plantillas'))
        ->assertRedirect(route('login'));
});

test('dashboard.plantillas devuelve el catálogo completo para un admin', function () {
    dashboardPlantillasTheme();

    $user = templateAdminUser();

    $this->actingAs($user)
        ->get(route('dashboard.plantillas'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('dashboard/plantillas/plantillas')
            ->has('templates', 1)
            ->has('surfaces')
            ->where('canCreate', true)
            ->where('canViewAll', true));
});

test('dashboard.plantillas devuelve 403 sin permiso view_templates', function () {
    dashboardPlantillasTheme();

    (new RolePermissionSeeder)->run();
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('dashboard.plantillas'))
        ->assertForbidden();
});

test('la antigua ruta templates.index ya no existe (GET /templates → 405)', function () {
    $user = templateAdminUser();

    $this->actingAs($user)
        ->get('/templates')
        ->assertMethodNotAllowed();
});
