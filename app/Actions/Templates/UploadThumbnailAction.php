<?php

namespace App\Actions\Templates;

use App\Data\Templates\TemplateManifestData;
use App\Services\Templates\TemplateManifestService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;

class UploadThumbnailAction
{
    public function __construct(
        protected TemplateManifestService $manifests,
    ) {}

    /**
     * Guarda la imagen de preview y actualiza thumbnail en template.json.
     *
     * @return array{message: string, thumbnail: string}
     */
    public function handle(TemplateManifestData $manifest, UploadedFile $file): array
    {
        $extension = strtolower($file->getClientOriginalExtension());
        $extension = in_array($extension, ['png', 'jpg', 'jpeg', 'webp', 'gif'], true) ? $extension : 'png';

        $thumbDir = $this->manifests->homePath($manifest->home).'/thumbnails';
        File::ensureDirectoryExists($thumbDir);

        $filename = 'preview.'.$extension;
        File::put($thumbDir.'/'.$filename, $file->get());

        $current = $manifest->toManifestArray();
        $next = $current;
        $next['thumbnail'] = 'thumbnails/'.$filename;

        $manifestPath = $this->manifests->homePath($manifest->home).'/template.json';
        File::put($manifestPath, json_encode($next, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        return [
            'message' => 'Preview subida.',
            'thumbnail' => $next['thumbnail'],
        ];
    }
}
