<?php

use App\Services\Templates\TemplateManifestService;
use App\Services\Templates\TemplateScaffoldService;
use Illuminate\Support\Facades\File;

beforeEach(function () {
    config(['templates.base_path' => 'storage/app/template-tests']);
    File::deleteDirectory(storage_path('app/template-tests'));
    File::makeDirectory(storage_path('app/template-tests'), 0755, true);
});

afterEach(function () {
    File::deleteDirectory(storage_path('app/template-tests'));
});

function templateTestBase(): string
{
    return storage_path('app/template-tests');
}

function makeTestTheme(array $types = ['landing', 'dashboard'], string $slug = 'aurora'): string
{
    $scaffold = app(TemplateScaffoldService::class);

    return $scaffold->make(slug: $slug, types: $types, name: 'Aurora', withCss: false);
}

test('template:make crea el home del tema con roles y theme de 3 paletas', function () {
    $home = makeTestTheme();

    expect($home)->toBe('landing/templates/aurora');

    $manifestPath = templateTestBase().'/'.$home.'/template.json';
    expect(File::exists($manifestPath))->toBeTrue();

    $manifest = json_decode(File::get($manifestPath), true);

    expect($manifest['type'])->toBe(['landing', 'dashboard']);
    expect($manifest['exports']['bundle'])->toBe('dist/aurora.js');
    expect(count($manifest['theme']['palettes']))->toBe(3);
    expect($manifest['theme']['default'])->toBe('indigo');

    // Home contiene assets/ y thumbnails/.
    expect(is_dir(templateTestBase().'/'.$home.'/assets'))->toBeTrue();
    expect(is_dir(templateTestBase().'/'.$home.'/thumbnails'))->toBeTrue();

    // Roles obligatorios por superficie existen.
    $service = app(TemplateManifestService::class);
    $dto = $service->loadBySlug('aurora');

    expect(array_keys($dto->components['landing']))->toContain('template', 'layout', 'header', 'footer', 'topmenu');
    expect(array_keys($dto->components['dashboard']))->toContain('template', 'layout', 'sidebar', 'topmenu');
});

test('make crea carpetas por superficie con TSX', function () {
    makeTestTheme();

    $surfaceFiles = [
        'landing/templates/aurora/aurora-template.tsx',
        'landing/templates/aurora/aurora-layout.tsx',
        'landing/templates/aurora/components/aurora-header.tsx',
        'dashboard/templates/aurora/aurora-dashboard-template.tsx',
        'dashboard/templates/aurora/aurora-dashboard-layout.tsx',
        'dashboard/templates/aurora/components/aurora-topmenu.tsx',
    ];

    foreach ($surfaceFiles as $file) {
        expect(File::exists(templateTestBase().'/'.$file))->toBeTrue("Falta {$file}");
    }
});

test('make genera css custom solo con --css', function () {
    makeTestTheme(['landing', 'auth'], 'no-css');
    expect(File::exists(templateTestBase().'/landing/templates/no-css/no-css.css'))->toBeFalse();

    $scaffold = app(TemplateScaffoldService::class);
    $scaffold->make(slug: 'with-css', types: ['landing'], name: 'Con CSS', withCss: true);

    expect(File::exists(templateTestBase().'/landing/templates/with-css/with-css.css'))->toBeTrue();
});

test('make rechaza slugs invalidos o sin superficie', function () {
    expect(fn () => app(TemplateScaffoldService::class)->make(slug: 'Bad Slug!', types: ['landing'], name: 'X'))
        ->toThrow(RuntimeException::class);

    expect(fn () => app(TemplateScaffoldService::class)->make(slug: 'ok', types: ['invalid'], name: 'X'))
        ->toThrow(RuntimeException::class);
});

test('rol de componente fuera de vocabulario falla la validacion', function () {
    $home = makeTestTheme(['landing']);
    $manifestPath = templateTestBase().'/'.$home.'/template.json';
    $manifest = json_decode(File::get($manifestPath), true);

    $manifest['components']['landing']['notarol'] = 'landing/templates/aurora/aurora-layout.tsx';
    File::put($manifestPath, json_encode($manifest));

    $service = app(TemplateManifestService::class);

    expect(fn () => $service->loadBySlug('aurora'))
        ->toThrow(RuntimeException::class, 'fuera del vocabulario');
});

test('JSON roto falla con mensaje del parser', function () {
    $home = makeTestTheme(['landing']);
    File::put(templateTestBase().'/'.$home.'/template.json', '{no es json');

    $service = app(TemplateManifestService::class);

    expect(fn () => $service->loadBySlug('aurora'))
        ->toThrow(RuntimeException::class, 'Error de sintaxis JSON');
});

test('theme con menos de 3 paletas falla', function () {
    $home = makeTestTheme(['landing']);
    $manifestPath = templateTestBase().'/'.$home.'/template.json';
    $manifest = json_decode(File::get($manifestPath), true);

    array_pop($manifest['theme']['palettes']);
    $manifest['theme']['palettes'] = array_values($manifest['theme']['palettes']);
    File::put($manifestPath, json_encode($manifest));

    $service = app(TemplateManifestService::class);

    expect(fn () => $service->loadBySlug('aurora'))
        ->toThrow(RuntimeException::class, 'al menos 3 paletas');
});

test('theme.default inexistente falla', function () {
    $home = makeTestTheme(['landing']);
    $manifestPath = templateTestBase().'/'.$home.'/template.json';
    $manifest = json_decode(File::get($manifestPath), true);

    $manifest['theme']['default'] = 'inexistente';
    File::put($manifestPath, json_encode($manifest));

    $service = app(TemplateManifestService::class);

    expect(fn () => $service->loadBySlug('aurora'))
        ->toThrow(RuntimeException::class, 'theme.default');
});

test('tema con theme ausente es valido (compatibilidad)', function () {
    $home = makeTestTheme(['landing']);
    $manifestPath = templateTestBase().'/'.$home.'/template.json';
    $manifest = json_decode(File::get($manifestPath), true);

    unset($manifest['theme']);
    File::put($manifestPath, json_encode($manifest));

    $service = app(TemplateManifestService::class);

    expect($service->loadBySlug('aurora')->theme)->toBeNull();
});

test('paleta con rol colores faltante usa fallback neutro (no es error)', function () {
    $home = makeTestTheme(['landing']);
    $manifestPath = templateTestBase().'/'.$home.'/template.json';
    $manifest = json_decode(File::get($manifestPath), true);

    unset($manifest['theme']['palettes'][0]['colors']['accent']);
    File::put($manifestPath, json_encode($manifest));

    $service = app(TemplateManifestService::class);

    expect($service->loadBySlug('aurora')->theme['palettes'][0]['colors'])
        ->not->toHaveKey('accent');
});

test('fixture del tema compilado valida OK en cada superficie', function () {
    $home = makeTestTheme(['landing', 'dashboard', 'forms', 'auth'], 'aurora');
    $service = app(TemplateManifestService::class);

    $dto = $service->loadBySlug('aurora');

    expect($dto->types)->toBe(['landing', 'dashboard', 'forms', 'auth']);
    expect($dto->home)->toBe($home);

    foreach ($dto->types as $type) {
        expect(array_keys($dto->components[$type]))->toContain('template', 'layout');
    }
});
