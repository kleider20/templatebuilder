<?php

namespace App\Http\Controllers\Templates;

use App\Actions\Templates\CreateTemplateAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Templates\StoreTemplateRequest;
use App\Models\Template;
use App\Services\Templates\TemplateManifestService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;
use RuntimeException;

class TemplateController extends Controller
{
    public function __construct(
        protected TemplateManifestService $manifests,
        protected CreateTemplateAction $create,
    ) {}

    /**
     * Formulario de creación de un tema nuevo.
     */
    public function create(): Response
    {
        $this->authorize('create', Template::class);

        return Inertia::render('templates/create', [
            'surfaces' => $this->manifests->surfaces(),
        ]);
    }

    /**
     * Crea un tema nuevo desde el formulario.
     */
    public function store(StoreTemplateRequest $request): RedirectResponse
    {
        $this->authorize('create', Template::class);

        $validated = $request->validated();

        try {
            $this->create->handle(
                slug: $validated['slug'],
                types: $validated['types'],
                name: $validated['name'],
                userId: $request->user()->id,
                withCss: (bool) ($validated['with_css'] ?? false),
            );
        } catch (RuntimeException $e) {
            return back()->withErrors(['slug' => $e->getMessage()]);
        }

        return redirect()->route('templates.editor', $validated['slug']);
    }
}
