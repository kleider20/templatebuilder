<?php

namespace App\Http\Controllers;

use App\Services\Templates\TemplateManifestService;
use App\Services\Templates\TemplateScaffoldService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use RuntimeException;

class TemplateController extends Controller
{
    public function __construct(
        protected TemplateManifestService $manifests,
        protected TemplateScaffoldService $scaffold,
    ) {}

    /**
     * Catálogo de temas (público o autenticado).
     */
    public function index(): Response
    {
        $templates = collect($this->manifests->scan())
            ->map(fn ($manifest) => [
                'name' => $manifest->name,
                'slug' => $manifest->slug,
                'version' => $manifest->version,
                'author' => $manifest->author,
                'description' => $manifest->description,
                'types' => $manifest->types,
                'thumbnail' => $this->thumbnailUrl($manifest->home, $manifest->thumbnail),
                'home' => $manifest->home,
                'settings' => $manifest->settings,
                'theme' => $manifest->theme,
            ])
            ->sortByDesc('name')
            ->values();

        return Inertia::render('templates/index', [
            'templates' => $templates,
            'surfaces' => $this->manifests->surfaces(),
        ]);
    }

    /**
     * Formulario de creación de un tema nuevo.
     */
    public function create(): Response
    {
        return Inertia::render('templates/create', [
            'surfaces' => $this->manifests->surfaces(),
        ]);
    }

    /**
     * Crea un tema nuevo desde el formulario.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'slug' => ['required', 'string', 'max:120', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/'],
            'types' => ['required', 'array', 'min:1'],
            'types.*' => ['required', 'string', 'in:landing,dashboard,forms,auth'],
            'with_css' => ['sometimes', 'boolean'],
        ]);

        try {
            $home = $this->scaffold->make(
                slug: $validated['slug'],
                types: $validated['types'],
                name: $validated['name'],
                withCss: (bool) ($validated['with_css'] ?? false),
            );
        } catch (RuntimeException $e) {
            return back()->withErrors(['slug' => $e->getMessage()]);
        }

        return redirect()->route('templates.editor', $validated['slug']);
    }

    /**
     * URL canónica del thumbnail: templates/{slug}/thumbnails/{file}.
     * Devuelve null si el archivo no existe (el frontend muestra placeholder).
     */
    protected function thumbnailUrl(string $home, ?string $thumbnail): ?string
    {
        if ($thumbnail === null) {
            return null;
        }

        $slug = basename($home);

        $file = pathinfo($thumbnail, PATHINFO_BASENAME);

        $path = $this->manifests->homePath($home).'/thumbnails/'.$file;

        if (! is_file($path)) {
            return null;
        }

        return url(config('templates.url_prefix').'/'.$slug.'/thumbnails/'.$file);
    }
}
