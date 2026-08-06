<?php

use App\Models\User;
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

function editorBase(): string
{
    return storage_path('app/template-tests');
}

test('editor index lista el árbol del tema', function () {
    app(TemplateScaffoldService::class)->make(slug: 'aurora', types: ['landing', 'dashboard'], name: 'Aurora');

    $user = User::factory()->create();

    $response = $this->actingAs($user)->get(route('templates.editor', 'aurora').'?path=landing/templates/aurora/aurora-template.tsx');

    $response->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('templates/editor')
            ->where('slug', 'aurora')
            ->has('tree'));
});

test('updateFile guarda el contenido de un archivo válido', function () {
    app(TemplateScaffoldService::class)->make(slug: 'aurora', types: ['landing'], name: 'Aurora');

    $user = User::factory()->create();
    $path = 'landing/templates/aurora/aurora-layout.tsx';

    $response = $this->actingAs($user)->putJson(route('templates.editor.update', 'aurora'), [
        'path' => $path,
        'content' => 'export default () => null;',
    ]);

    $response->assertOk()->assertJson(['message' => 'Archivo guardado.']);
    expect(File::get(editorBase().'/'.$path))->toContain('export default');
});

test('updateFile rechaza path con ..', function () {
    app(TemplateScaffoldService::class)->make(slug: 'aurora', types: ['landing'], name: 'Aurora');

    $user = User::factory()->create();

    $this->actingAs($user)->putJson(route('templates.editor.update', 'aurora'), [
        'path' => 'landing/templates/aurora/../../secret.txt',
        'content' => 'hack',
    ])->assertUnprocessable();
});

test('updateFile rechaza rutas fuera del tema', function () {
    app(TemplateScaffoldService::class)->make(slug: 'aurora', types: ['landing'], name: 'Aurora');

    $user = User::factory()->create();

    $this->actingAs($user)->putJson(route('templates.editor.update', 'aurora'), [
        'path' => 'dashboard/templates/other/file.tsx',
        'content' => 'hack',
    ])->assertUnprocessable();
});

test('updateFile rechaza extensiones fuera de allowlist', function () {
    app(TemplateScaffoldService::class)->make(slug: 'aurora', types: ['landing'], name: 'Aurora');

    $user = User::factory()->create();

    $this->actingAs($user)->putJson(route('templates.editor.update', 'aurora'), [
        'path' => 'landing/templates/aurora/aurora.evil',
        'content' => 'hack',
    ])->assertUnprocessable();
});
