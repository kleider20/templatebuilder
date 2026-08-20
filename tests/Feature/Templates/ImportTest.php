<?php

use App\Models\Template;
use App\Models\User;
use App\Services\Templates\TemplateImportService;
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

function importTestBase(): string
{
    return storage_path('app/template-tests');
}

/**
 * Crea un tema real y lo empaqueta como ZIP de export (template.json al root
 * + carpeta del tema con dist/ compilado). Devuelve la ruta del ZIP.
 */
function makeImportZip(string $slug = 'aurora'): string
{
    $scaffold = app(TemplateScaffoldService::class);
    $home = $scaffold->make(slug: $slug, types: ['landing', 'dashboard'], name: 'Aurora');

    $base = importTestBase();

    $distDir = $base.'/'.$home.'/dist';
    File::makeDirectory($distDir, 0755, true);
    File::put($distDir.'/'.$slug.'.js', 'export const components = {};');
    File::put($distDir.'/'.$slug.'.css', '--color-primary: var(--tpl-primary);.dark{}');

    $zipPath = storage_path('app/import-source-'.$slug.'.zip');
    @unlink($zipPath);

    $zip = new ZipArchive;
    $zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE);

    $zip->addFile($base.'/'.$home.'/template.json', 'template.json');

    foreach (File::allFiles($base.'/'.$home) as $file) {
        $relative = str_replace('\\', '/', $file->getRelativePathname());
        $zip->addFile($file->getPathname(), $home.'/'.$relative);
    }

    $zip->close();

    return $zipPath;
}

test('importFromZip copia el tema y valida el manifiesto', function () {
    $zipPath = makeImportZip('aurora');

    // Simulamos un proyecto limpio eliminando el tema del destino.
    File::deleteDirectory(importTestBase().'/aurora');

    $manifest = app(TemplateImportService::class)->importFromZip($zipPath);

    expect($manifest->slug)->toBe('aurora');
    expect(File::isDirectory(importTestBase().'/aurora'))->toBeTrue();
    expect(File::exists(importTestBase().'/aurora/template.json'))->toBeTrue();
    expect(File::exists(importTestBase().'/aurora/landing-aurora-template.tsx'))->toBeTrue();
    expect(File::exists(importTestBase().'/aurora/dist/aurora.js'))->toBeTrue();

    File::delete($zipPath);
});

test('importFromZip falla si el tema ya existe sin --force', function () {
    $zipPath = makeImportZip('aurora');

    expect(fn () => app(TemplateImportService::class)->importFromZip($zipPath))
        ->toThrow(RuntimeException::class, 'ya existe');

    File::delete($zipPath);
});

test('importFromZip con force sobrescribe el tema existente', function () {
    $zipPath = makeImportZip('aurora');

    File::put(importTestBase().'/aurora/marcador.txt', 'viejo');

    app(TemplateImportService::class)->importFromZip($zipPath, force: true);

    expect(File::exists(importTestBase().'/aurora/marcador.txt'))->toBeFalse();

    File::delete($zipPath);
});

test('importFromSlug registra la propiedad en la tabla templates', function () {
    app(TemplateScaffoldService::class)->make(slug: 'aurora', types: ['landing'], name: 'Aurora');

    $user = User::factory()->create();

    app(TemplateImportService::class)->importFromSlug('aurora', $user->id);

    expect(Template::where('slug', 'aurora')->first()->user_id)->toBe($user->id);
});

test('importFromZip rechaza un ZIP sin template.json en la raíz', function () {
    $zipPath = storage_path('app/bad.zip');
    @unlink($zipPath);

    $zip = new ZipArchive;
    $zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE);
    $zip->addFromString('foo.txt', 'hola');
    $zip->close();

    expect(fn () => app(TemplateImportService::class)->importFromZip($zipPath))
        ->toThrow(RuntimeException::class, 'template.json');

    File::delete($zipPath);
});
