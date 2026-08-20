<?php

namespace App\Http\Controllers\Dashboard;

use App\Actions\Templates\CatalogTemplatesAction;
use App\Http\Controllers\Controller;
use App\Models\Template;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardPlantillasController extends Controller
{
    public function __construct(
        protected CatalogTemplatesAction $catalog,
    ) {}

    /**
     * Catálogo de plantillas del dashboard (canónico; sustituye a la antigua templates/index).
     */
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Template::class);

        $data = $this->catalog->handle($request->user());

        return Inertia::render('dashboard/plantillas/plantillas', [
            'templates' => $data['items'],
            'surfaces' => $data['surfaces'],
            'canCreate' => $data['canCreate'],
            'canViewAll' => $data['canViewAll'],
        ]);
    }
}
