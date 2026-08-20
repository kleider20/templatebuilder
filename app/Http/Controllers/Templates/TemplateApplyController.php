<?php

namespace App\Http\Controllers\Templates;

use App\Actions\Templates\ApplyThemeAction;
use App\Http\Controllers\Controller;
use App\Models\Template;
use App\Services\Templates\TemplateManifestService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use RuntimeException;

class TemplateApplyController extends Controller
{
    public function __construct(
        protected TemplateManifestService $manifests,
        protected ApplyThemeAction $apply,
    ) {}

    /**
     * Aplica un tema al proyecto (global) en las superficies que soporta.
     */
    public function store(Request $request, string $slug): RedirectResponse
    {
        try {
            $manifest = $this->manifests->loadBySlug($slug);
        } catch (RuntimeException) {
            abort(404);
        }

        $template = Template::where('slug', $slug)->first();

        if ($template !== null) {
            $this->authorize('update', $template);
        } else {
            abort_if(! $request->user()->hasPermissionTo('manage_templates'), 403);
        }

        $applied = $this->apply->handle($manifest);

        return redirect()->back()->with('status', __('Tema aplicado a: :surfaces', [
            'surfaces' => implode(', ', $applied),
        ]));
    }
}