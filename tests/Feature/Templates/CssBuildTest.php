<?php

use Illuminate\Support\Facades\File;

test('el CSS dist del tema aurora mapea roles y tokens semánticos a --tpl-*', function () {
    $path = resource_path('js/layouts/aurora/dist/aurora.css');

    if (! File::exists($path)) {
        $this->markTestSkipped('Requiere el tema aurora compilado en el repositorio.');

        return;
    }

    $css = File::get($path);

    // Roles del tema mapeados a las variables de paleta.
    expect($css)->toContain('--color-primary: var(--tpl-primary)');
    expect($css)->toContain('--color-content: var(--tpl-text)');
    expect($css)->toContain('--color-card: var(--tpl-surface)');
    expect($css)->toContain('--color-border:');

    // Utilidades generadas para las clases usadas por los layouts del tema.
    expect($css)->toContain('.text-content {')
        ->toContain('color: var(--tpl-text);');
    expect($css)->toContain('.border-border {')
        ->toContain('border-color: var(--tpl-text);');
    expect($css)->toContain('.bg-card {')
        ->toContain('background-color: var(--tpl-surface);');
    expect($css)->toContain('.border-input {')
        ->toContain('border-color: var(--tpl-surface);');
    expect($css)->toContain('.bg-surface {')
        ->toContain('background-color: var(--tpl-surface);');
    expect($css)->toContain('.text-muted-foreground {')
        ->toContain('color: var(--tpl-text);');
    expect($css)->toContain('.text-primary {')
        ->toContain('color: var(--tpl-primary);');
});
