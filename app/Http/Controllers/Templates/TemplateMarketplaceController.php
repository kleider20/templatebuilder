<?php

namespace App\Http\Controllers\Templates;

use App\Actions\Templates\MarketplaceTemplatesAction;
use App\Http\Controllers\Controller;
use App\Models\TemplateVersion;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * Marketplace público de temas: lista todos los manifiestos con su pricing y
 * permite descargar el último ZIP exportado. No exige autenticación.
 */
class TemplateMarketplaceController extends Controller
{
    public function __construct(
        protected MarketplaceTemplatesAction $marketplace,
    ) {}

    /**
     * GET /marketplace — catálogo público de temas con FREE/precio.
     */
    public function index(): Response
    {
        return Inertia::render('templates/marketplace', [
            'templates' => $this->marketplace->handle(),
        ]);
    }

    /**
     * GET /marketplace/{slug}/download — descarga el último ZIP exportado.
     */
    public function download(string $slug): BinaryFileResponse
    {
        $version = TemplateVersion::query()
            ->where('slug', $slug)
            ->latest('created_at')
            ->first();

        if ($version === null) {
            abort(404, 'No hay una versión exportada para este tema.');
        }

        $path = base_path($version->zip_path);

        if (! is_file($path)) {
            abort(404, 'El ZIP exportado no está disponible.');
        }

        return response()->download($path, basename($path));
    }
}
