<?php

namespace App\Actions\Templates;

use App\Data\Templates\TemplateManifestData;
use App\Data\Templates\TemplateTreeNodeData;
use App\Services\Templates\TemplateManifestService;
use Illuminate\Support\Facades\File;

class BuildEditorTreeAction
{
    public function __construct(
        protected TemplateManifestService $manifests,
    ) {}

    /**
     * Árbol multi-raíz del editor (una raíz por superficie del tema).
     *
     * @return array<int, TemplateTreeNodeData>
     */
    public function handle(TemplateManifestData $manifest): array
    {
        $tree = [];

        foreach ($manifest->types as $surface) {
            $root = $this->manifests->basePath().'/'.$surface.'/templates/'.$manifest->slug;

            if (! is_dir($root)) {
                continue;
            }

            $tree = array_merge($tree, $this->walk($root, $root, $surface.'/templates/'.$manifest->slug));
        }

        return $tree;
    }

    /**
     * Recorre un directorio y devuelve nodos planos para el árbol del editor.
     *
     * @return array<int, TemplateTreeNodeData>
     */
    protected function walk(string $root, string $dir, string $prefix): array
    {
        $nodes = [];

        $entries = collect(File::allFiles($dir))
            ->sortBy(fn ($file) => $file->getFilename())
            ->values();

        foreach ($entries as $file) {
            $extension = $file->getExtension();

            if (! in_array($extension, (array) config('templates.editor_extensions'), true)) {
                continue;
            }

            $relative = str_replace('\\', '/', substr($file->getPathname(), strlen($root) + 1));

            $nodes[] = new TemplateTreeNodeData(
                name: $file->getFilename(),
                type: 'file',
                path: $prefix.'/'.$relative,
            );
        }

        return $nodes;
    }
}
