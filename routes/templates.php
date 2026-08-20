<?php

use App\Http\Controllers\Templates\TemplateApplyController;
use App\Http\Controllers\Templates\TemplateController;
use App\Http\Controllers\Templates\TemplateEditorController;
use App\Http\Controllers\Templates\TemplateExportController;
use App\Http\Controllers\Templates\TemplateMarketplaceController;
use App\Http\Controllers\Templates\TemplatePreviewController;
use App\Http\Controllers\Templates\TemplateViewController;
use App\Http\Middleware\NoStore;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Template Builder — Catálogo, gestión, preview y paridad de URLs
|--------------------------------------------------------------------------
*/

Route::prefix('templates')->name('templates.')->group(function () {
    // Gestión y preview (requieren autenticación + permiso "view_templates").
    // El catálogo ya no vive aquí: vive en dashboard.plantillas (dashboard/plantillas).
    Route::middleware(['auth', 'verified'])->group(function () {
        Route::get('create', [TemplateController::class, 'create'])->name('create');
        Route::post('/', [TemplateController::class, 'store'])->name('store');

        Route::post('{slug}/apply', [TemplateApplyController::class, 'store'])->name('apply');

        Route::get('versions', [TemplateExportController::class, 'index'])->name('versions.index');
        Route::get('versions/{version}/download', [TemplateExportController::class, 'download'])
            ->name('versions.download');

        Route::get('{slug}/editor', [TemplateEditorController::class, 'show'])->name('editor');
        Route::put('{slug}/editor', [TemplateEditorController::class, 'update'])->name('editor.update');

        Route::put('{slug}/manifest', [TemplateEditorController::class, 'updateManifest'])
            ->name('manifest.update');

        Route::post('{slug}/thumbnail', [TemplateEditorController::class, 'uploadThumbnail'])
            ->name('thumbnail.update');

        Route::get('{slug}/preview', [TemplatePreviewController::class, 'show'])
            ->middleware(NoStore::class)
            ->name('preview.show');

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

/*
|--------------------------------------------------------------------------
| Marketplace público — listado de temas con FREE/precio y descarga
|--------------------------------------------------------------------------
*/
Route::get('marketplace', [TemplateMarketplaceController::class, 'index'])->name('marketplace');
Route::get('marketplace/{slug}/download', [TemplateMarketplaceController::class, 'download'])
    ->where('slug', '[a-z0-9-]+')
    ->name('marketplace.download');
