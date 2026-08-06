<?php

use App\Http\Controllers\Templates\TemplateController;
use App\Http\Controllers\Templates\TemplateEditorController;
use App\Http\Controllers\Templates\TemplateExportController;
use App\Http\Controllers\Templates\TemplatePreviewController;
use App\Http\Controllers\Templates\TemplateViewController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Template Builder — Catálogo, gestión, preview y paridad de URLs
|--------------------------------------------------------------------------
*/

Route::prefix('templates')->name('templates.')->group(function () {
    // Catálogo y preview (requieren autenticación + permiso "view_templates").
    Route::middleware(['auth', 'verified'])->group(function () {
        Route::get('/', [TemplateController::class, 'index'])->name('index');
        Route::get('create', [TemplateController::class, 'create'])->name('create');
        Route::post('/', [TemplateController::class, 'store'])->name('store');

        Route::get('versions', [TemplateExportController::class, 'index'])->name('versions.index');
        Route::get('versions/{version}/download', [TemplateExportController::class, 'download'])
            ->name('versions.download');

        Route::get('{slug}/editor', [TemplateEditorController::class, 'show'])->name('editor');
        Route::put('{slug}/editor', [TemplateEditorController::class, 'update'])->name('editor.update');

        Route::put('{slug}/manifest', [TemplateEditorController::class, 'updateManifest'])
            ->name('manifest.update');

        Route::post('{slug}/thumbnail', [TemplateEditorController::class, 'uploadThumbnail'])
            ->name('thumbnail.update');

        Route::get('{slug}/preview', [TemplatePreviewController::class, 'show'])->name('preview.show');

        Route::post('{slug}/preview', [TemplatePreviewController::class, 'data'])->name('preview');

        Route::post('{slug}/export', [TemplateExportController::class, 'store'])->name('export');
    });
});

// URLs canónicas de artefactos: idénticas a las del sandbox / proyectos finales.
// Se registran después del grupo de gestión y son PÚBLICAS (artefactos consumidos).
Route::get('templates/{slug}/{dir}/{file}', [TemplateViewController::class, 'show'])
    ->where('dir', 'dist|assets|thumbnails')
    ->name('templates.parity.show');

Route::get('templates/{slug}/assets/{path}', [TemplateViewController::class, 'asset'])
    ->where('path', '.*')
    ->name('templates.parity.asset');
