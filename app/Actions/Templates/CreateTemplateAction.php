<?php

namespace App\Actions\Templates;

use App\Models\Template;
use App\Services\Templates\TemplateScaffoldService;
use RuntimeException;

class CreateTemplateAction
{
    public function __construct(
        protected TemplateScaffoldService $scaffold,
    ) {}

    /**
     * Crea un tema nuevo y registra al usuario como dueño (tabla templates).
     *
     * @param  array<int, string>  $types
     *
     * @throws RuntimeException
     */
    public function handle(
        string $slug,
        array $types,
        string $name,
        int $userId,
        bool $withCss = false,
    ): string {
        $home = $this->scaffold->make(
            slug: $slug,
            types: $types,
            name: $name,
            withCss: $withCss,
        );

        Template::create([
            'slug' => $slug,
            'name' => $name,
            'user_id' => $userId,
        ]);

        return $home;
    }
}
