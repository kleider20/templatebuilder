<?php

namespace App\Data\Templates;

use App\Models\TemplateVersion;
use Spatie\LaravelData\Data;

/**
 * Fila del historial de versiones exportadas (templates/versions).
 */
class TemplateVersionData extends Data
{
    /**
     * @param  array<string, mixed>|null  $manifest
     */
    public function __construct(
        public int $id,
        public string $slug,
        public string $version,
        public ?array $manifest,
        public string $zip_path,
        public string $checksum,
        public ?string $createdAt,
        public string $downloadUrl,
    ) {}

    public static function fromVersion(TemplateVersion $version): self
    {
        return new self(
            id: $version->id,
            slug: $version->slug,
            version: $version->version,
            manifest: $version->manifest,
            zip_path: $version->zip_path,
            checksum: $version->checksum,
            createdAt: $version->created_at?->toISOString(),
            downloadUrl: route('templates.versions.download', $version->id),
        );
    }
}
