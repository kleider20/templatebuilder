<?php

use App\Http\Controllers\TemplateController;
use App\Http\Controllers\TemplateEditorController;
use App\Http\Controllers\TemplateExportController;
use App\Http\Controllers\TemplateManifestEditorController;
use App\Http\Controllers\TemplatePreviewController;
use App\Http\Controllers\TemplateViewController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Template Builder — Catálogo, gestión, preview y paridad de URLs
|--------------------------------------------------------------------------
*/

// Catálogo público.
Route::get('templates', [TemplateController::class, 'index'])->name('templates.index');
Route::get('templates/{slug}/preview', [TemplatePreviewController::class, 'show'])->name('templates.preview.show');

// Rutas de gestión: se registran ANTES de la ruta de paridad para no ser
// capturadas por templates/{slug}/{dir}/{file}.
Route::middleware(['auth'])->prefix('templates')->group(function () {
    Route::get('create', [TemplateController::class, 'create'])->name('templates.create');
    Route::post('/', [TemplateController::class, 'store'])->name('templates.store');

    Route::get('versions', [TemplateExportController::class, 'index'])->name('templates.versions.index');
    Route::get('versions/{version}/download', [TemplateExportController::class, 'download'])
        ->name('templates.versions.download');

    Route::get('{slug}/editor', [TemplateEditorController::class, 'show'])->name('templates.editor');
    Route::put('{slug}/editor', [TemplateEditorController::class, 'update'])->name('templates.editor.update');

    Route::put('{slug}/manifest', [TemplateManifestEditorController::class, 'update'])
        ->name('templates.manifest.update');

    Route::post('{slug}/thumbnail', [TemplateEditorController::class, 'uploadThumbnail'])
        ->name('templates.thumbnail.update');

    Route::post('{slug}/preview', [TemplatePreviewController::class, 'data'])->name('templates.preview');

    Route::post('{slug}/export', [TemplateExportController::class, 'store'])->name('templates.export');
});

// URLs canónicas de artefactos: idénticas a las del sandbox / proyectos finales.
Route::get('templates/{slug}/{dir}/{file}', [TemplateViewController::class, 'show'])
    ->where('dir', 'dist|assets|thumbnails')
    ->name('templates.parity.show');

Route::get('templates/{slug}/assets/{path}', [TemplateViewController::class, 'asset'])
    ->where('path', '.*')
    ->name('templates.parity.asset');
