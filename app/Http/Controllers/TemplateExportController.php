<?php

namespace App\Http\Controllers;

use App\Models\TemplateVersion;
use App\Services\Templates\TemplateExportService;
use App\Services\Templates\TemplateManifestService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class TemplateExportController extends Controller
{
    public function __construct(
        protected TemplateExportService $export,
        protected TemplateManifestService $manifests,
    ) {}

    /**
     * Historial de versiones de un slug (para el catálogo / versiones).
     */
    public function index(Request $request): Response
    {
        $versions = TemplateVersion::query()
            ->where('slug', $request->query('slug', $request->route('slug') ?? ''))
            ->orderByDesc('created_at')
            ->get()
            ->map(fn ($version) => [
                'id' => $version->id,
                'slug' => $version->slug,
                'version' => $version->version,
                'manifest' => $version->manifest,
                'zip_path' => $version->zip_path,
                'checksum' => $version->checksum,
                'created_at' => $version->created_at?->toISOString(),
                'download_url' => route('templates.versions.download', $version->id),
            ]);

        return Inertia::render('templates/versions', [
            'slug' => $request->query('slug'),
            'versions' => $versions,
        ]);
    }

    /**
     * Álmacena una nueva versión exportada de un tema.
     */
    public function store(Request $request, string $slug): RedirectResponse
    {
        $validated = $request->validate([
            'slug' => ['required', 'string', 'max:120'],
        ]);

        try {
            $version = $this->export->export($validated['slug'], $request->user()?->id);
        } catch (\RuntimeException $e) {
            Inertia::flash('toast', ['type' => 'error', 'message' => $e->getMessage()]);

            return back();
        }

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => "Tema [{$version->slug}] v{$version->version} exportado.",
        ]);

        return back();
    }

    /**
     * Descarga el ZIP de una versión.
     */
    public function download(Request $request, int $version): BinaryFileResponse|SymfonyResponse
    {
        $record = TemplateVersion::findOrFail($version);

        $path = base_path($record->zip_path);

        if (! is_file($path)) {
            abort(404);
        }

        return response()->download($path, basename($path));
    }
}
