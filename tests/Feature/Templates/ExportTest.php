<?php

use App\Models\TemplateVersion;
use App\Services\Templates\TemplateExportService;
use App\Services\Templates\TemplateManifestService;
use App\Services\Templates\TemplateScaffoldService;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Process;

beforeEach(function () {
    config(['templates.base_path' => 'storage/app/template-tests']);
    File::deleteDirectory(storage_path('app/template-tests'));
    File::makeDirectory(storage_path('app/template-tests'), 0755, true);
});

afterEach(function () {
    File::deleteDirectory(storage_path('app/template-tests'));
});

function exportTestBase(): string
{
    return storage_path('app/template-tests');
}

/**
 * Crea un tema real en el disco de pruebas con sus TSX.
 */
function createExportTheme(string $slug = 'aurora'): string
{
    $scaffold = app(TemplateScaffoldService::class);
    $home = $scaffold->make(slug: $slug, types: ['landing', 'dashboard'], name: 'Aurora');

    // Añadimos un asset de ejemplo en el home.
    $assets = exportTestBase().'/'.$home.'/assets';
    File::put($assets.'/logo.svg', '<svg xmlns="http://www.w3.org/2000/svg"></svg>');

    return $home;
}

/**
 * Ejecuta el build real (necesita el tema real en resources/js/layouts).
 * Para tests de aislamiento usamos un stub del comando.
 */
test('parity route sirve dist y assets desde el home', function () {
    // Usamos el tema real generado en el repositorio (landing/templates/aurora).
    $this->markTestSkipped('Requiere el tema aurora compilado en el repositorio.');
});

test('export escribe output/{slug}-{version}.zip y registra TemplateVersion', function () {
    $home = createExportTheme('aurora');
    $manifestService = app(TemplateManifestService::class);
    $manifest = $manifestService->loadBySlug('aurora');

    // Stub del comando de build: creamos los artefactos esperados.
    $distDir = exportTestBase().'/'.$home.'/dist';
    File::makeDirectory($distDir, 0755, true);
    File::put($distDir.'/aurora.js', 'import{jsx}from"react/jsx-runtime";export const components={landing:{},dashboard:{}};');
    File::put($distDir.'/aurora.css', '--color-primary: var(--tpl-primary);.dark{}');

    Process::fake();

    $export = app(TemplateExportService::class);
    $version = $export->export('aurora');

    expect($version)->toBeInstanceOf(TemplateVersion::class);
    expect($version->slug)->toBe('aurora');
    expect($version->version)->toBe('1.0.0');
    expect(strlen($version->checksum))->toBe(64);
    expect($version->zip_path)->toBe('output/aurora-1.0.0.zip');

    $zipPath = base_path('output/aurora-1.0.0.zip');
    expect(File::exists($zipPath))->toBeTrue();

    // Limpieza
    File::delete($zipPath);
});

test('zip verbatim contiene template.json y los tsx de cada superficie', function () {
    $home = createExportTheme('aurora');
    $distDir = exportTestBase().'/'.$home.'/dist';
    File::makeDirectory($distDir, 0755, true);
    File::put($distDir.'/aurora.js', 'import{jsx}from"react/jsx-runtime";export const components={landing:{},dashboard:{}};');
    File::put($distDir.'/aurora.css', '--color-primary: var(--tpl-primary);.dark{}');

    Process::fake();

    $export = app(TemplateExportService::class);
    $export->export('aurora');

    $zipPath = base_path('output/aurora-1.0.0.zip');

    $zip = new ZipArchive;
    expect($zip->open($zipPath))->toBeTrue();

    // Verificamos que cada .tsx del ZIP es idéntico al original.
    $sourceFiles = collect(File::allFiles(exportTestBase()))
        ->filter(fn ($file) => $file->getExtension() === 'tsx' || $file->getBasename() === 'template.json')
        ->filter(fn ($file) => ! str_contains($file->getPathname(), '/dist/'));

    foreach ($sourceFiles as $file) {
        $relative = str_replace('\\', '/', $file->getRelativePathname());
        $localName = $relative;

        expect($zip->getFromName($localName))
            ->toBe(File::get($file->getPathname()), "Mismatch en {$localName}");
    }

    expect($zip->getFromName('template.json'))->toContain('"slug": "aurora"');
    expect($zip->getFromName('landing/templates/aurora/dist/aurora.js'))->not->toBeFalse();
    expect($zip->getFromName('landing/templates/aurora/dist/aurora.css'))->not->toBeFalse();

    $zip->close();
    File::delete($zipPath);
});
