<?php

namespace App\Actions\Templates;

use App\Data\Templates\TemplateSummaryData;
use App\Models\Template;
use App\Models\User;
use App\Services\Templates\TemplateManifestService;

class CatalogTemplatesAction
{
    public function __construct(
        protected TemplateManifestService $manifests,
    ) {}

    /**
     * Catálogo de temas. Un usuario normal ve sus propios temas; quien tenga
     * "view_all_templates" (o super_admin) ve los de todos los usuarios.
     *
     * @return array{items: array<int, array<string, mixed>>, surfaces: array<int, string>, canCreate: bool, canViewAll: bool}
     */
    public function handle(User $user): array
    {
        $owners = Template::pluck('user_id', 'slug')->toArray();

        $canViewAll = $user->hasPermissionTo('view_all_templates');
        $canCreate = $user->hasPermissionTo('create_templates');

        $items = collect($this->manifests->scan())
            ->filter(function ($manifest) use ($owners, $user, $canViewAll) {
                if ($canViewAll) {
                    return true;
                }

                return ($owners[$manifest->slug] ?? null) === $user->id;
            })
            ->sortByDesc(fn ($manifest) => $manifest->name)
            ->map(fn ($manifest) => TemplateSummaryData::from([
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
                'pricing' => $manifest->pricing,
            ])->toArray())
            ->values()
            ->all();

        return [
            'items' => $items,
            'surfaces' => $this->manifests->surfaces(),
            'canCreate' => $canCreate,
            'canViewAll' => $canViewAll,
        ];
    }

    /**
     * URL canónica del thumbnail o null si no existe.
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
