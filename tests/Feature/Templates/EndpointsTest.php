<?php

use App\Services\Templates\TemplateScaffoldService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;

beforeEach(function () {
    config(['templates.base_path' => 'storage/app/template-tests']);
    File::deleteDirectory(storage_path('app/template-tests'));
    File::makeDirectory(storage_path('app/template-tests'), 0755, true);
});

afterEach(function () {
    File::deleteDirectory(storage_path('app/template-tests'));
});

function endpointTestBase(): string
{
    return storage_path('app/template-tests');
}

function endpointTheme(string $slug = 'aurora'): string
{
    return app(TemplateScaffoldService::class)->make(slug: $slug, types: ['landing', 'dashboard'], name: 'Aurora');
}

test('endpoints de gestión exigen autenticación (401)', function () {
    endpointTheme();

    $routes = [
        'editor' => fn () => $this->get(route('templates.editor', 'aurora')),
        'editorUpdate' => fn () => $this->put(route('templates.editor.update', 'aurora'), ['path' => 'x', 'content' => 'x']),
        'manifest' => fn () => $this->put(route('templates.manifest.update', 'aurora'), ['settings' => []]),
        'export' => fn () => $this->post(route('templates.export', 'aurora')),
        'previewData' => fn () => $this->post(route('templates.preview', 'aurora')),
        'versions' => fn () => $this->get(route('templates.versions.index')),
        'thumbnail' => fn () => $this->post(route('templates.thumbnail.update', 'aurora'), [], ['X-Requested-With' => 'XMLHttpRequest']),
    ];

    foreach ($routes as $name => $call) {
        $call()->assertRedirect(route('login'));
    }
});

test('preview con paleta inexistente devuelve 422', function () {
    endpointTheme();

    $user = templateAdminUser();

    $this->actingAs($user)
        ->postJson(route('templates.preview', 'aurora'), ['palette' => 'no-existe'])
        ->assertUnprocessable();
});

test('preview con paleta válida devuelve la selección', function () {
    endpointTheme();

    $user = templateAdminUser();

    $this->actingAs($user)
        ->postJson(route('templates.preview', 'aurora'), ['palette' => 'indigo'])
        ->assertOk()
        ->assertJson(['palette' => 'indigo']);
});

test('editor de manifiesto valida settings y theme contra el contrato', function () {
    endpointTheme();

    $user = templateAdminUser();

    $this->actingAs($user)
        ->putJson(route('templates.manifest.update', 'aurora'), [
            'theme' => ['roles' => ['primary']],
        ])
        ->assertUnprocessable();
});

test('subir thumbnail sin archivo devuelve 422', function () {
    endpointTheme();

    $user = templateAdminUser();

    $this->actingAs($user)
        ->postJson(route('templates.thumbnail.update', 'aurora'), [])
        ->assertUnprocessable();
});

test('subir thumbnail con archivo no imagen devuelve 422', function () {
    endpointTheme();

    $user = templateAdminUser();

    $file = UploadedFile::fake()->create('preview.txt', 10);

    $this->actingAs($user)
        ->post(route('templates.thumbnail.update', 'aurora'), ['file' => $file])
        ->assertStatus(302);
});

test('subir thumbnail guarda la imagen en thumbnails/ y actualiza template.json', function () {
    $home = endpointTheme();

    $user = templateAdminUser();

    $file = UploadedFile::fake()->image('mi-preview.png', 640, 360);

    $this->actingAs($user)
        ->post(route('templates.thumbnail.update', 'aurora'), ['file' => $file])
        ->assertOk()
        ->assertJson(['thumbnail' => 'thumbnails/preview.png']);

    $homeDir = endpointTestBase().'/'.$home;
    $this->assertFileExists("$homeDir/thumbnails/preview.png");

    $manifest = json_decode(File::get("$homeDir/template.json"), true);
    $this->assertSame('thumbnails/preview.png', $manifest['thumbnail'] ?? null);
});
