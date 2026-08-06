<?php

namespace App\Http\Controllers\Templates;

use App\Actions\Templates\ExportTemplateAction;
use App\Data\Templates\TemplateVersionData;
use App\Http\Controllers\Controller;
use App\Http\Requests\Templates\ExportTemplateRequest;
use App\Models\Template;
use App\Models\TemplateVersion;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use RuntimeException;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class TemplateExportController extends Controller
{
    public function __construct(
        protected ExportTemplateAction $export,
    ) {}

    /**
     * Historial de versiones de un slug (para el catálogo / versiones).
     */
    public function index(Request $request): Response
    {
        $slug = (string) ($request->query('slug', ''));

        $this->authorize('view', $this->templateFor($slug));

        $versions = TemplateVersion::query()
            ->where('slug', $slug)
            ->orderByDesc('created_at')
            ->get()
            ->map(fn ($version) => TemplateVersionData::fromVersion($version)->toArray());

        return Inertia::render('templates/versions', [
            'slug' => $slug ?: null,
            'versions' => $versions,
        ]);
    }

    /**
     * Almacena una nueva versión exportada de un tema (usando el slug de la URL).
     */
    public function store(ExportTemplateRequest $request, string $slug): RedirectResponse
    {
        try {
            $this->authorize('export', $this->templateFor($slug));
        } catch (\Throwable $e) {
            abort(403);
        }

        try {
            $version = $this->export->handle($slug, $request->user()->id);
        } catch (RuntimeException $e) {
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
     * Descarga el ZIP de una versión (solo el dueño o quien ve todos).
     */
    public function download(Request $request, int $version): BinaryFileResponse|SymfonyResponse
    {
        $record = TemplateVersion::findOrFail($version);

        try {
            $this->authorize('view', $this->templateFor($record->slug));
        } catch (\Throwable $e) {
            abort(403);
        }

        $path = base_path($record->zip_path);

        if (! is_file($path)) {
            abort(404);
        }

        return response()->download($path, basename($path));
    }

    /**
     * Registro de propiedad del tema o registro vacío.
     */
    protected function templateFor(string $slug): Template
    {
        return Template::firstOrNew(['slug' => $slug]);
    }
}
