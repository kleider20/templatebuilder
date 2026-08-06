<?php

namespace App\Actions\Templates;

use App\Data\Templates\TemplateManifestData;
use Illuminate\Support\Facades\File;
use RuntimeException;

class SaveTemplateFileAction
{
    public function __construct(
        protected ResolveTemplateFileAction $resolver,
    ) {}

    /**
     * Guarda el contenido de un archivo del tema con containment.
     *
     * @throws RuntimeException
     */
    public function handle(string $relPath, string $content, TemplateManifestData $manifest): void
    {
        $absolute = $this->resolver->handle($relPath, $manifest);

        File::put($absolute, $content);
    }
}
