<?php

namespace App\Actions\Templates;

use App\Data\Templates\TemplateManifestData;
use RuntimeException;

class ReadTemplateFileAction
{
    public function __construct(
        protected ResolveTemplateFileAction $resolver,
    ) {}

    /**
     * Lee un archivo del tema de forma segura.
     *
     * @return array{path: string, content: string}|null
     */
    public function handle(string $relPath, TemplateManifestData $manifest): ?array
    {
        try {
            $absolute = $this->resolver->handle($relPath, $manifest);
        } catch (RuntimeException) {
            return null;
        }

        return [
            'path' => $relPath,
            'content' => file_get_contents($absolute),
        ];
    }
}
