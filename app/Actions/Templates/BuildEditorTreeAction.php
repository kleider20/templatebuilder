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
     * Árbol plano del editor para un tema: recorre el home una sola vez.
     *
     * @return array<int, TemplateTreeNodeData>
     */
    public function handle(TemplateManifestData $manifest): array
    {
        $root = $this->manifests->homePath($manifest->home);

        if (! is_dir($root)) {
            return [];
        }

        return $this->walk($root, $root, $manifest->home);
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
