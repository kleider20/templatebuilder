<?php

namespace App\Console\Commands;

use App\Services\Templates\TemplateScaffoldService;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('template:make {slug} {--type=*} {--css}')]
#[Description('Crea la estructura de un tema del template builder')]
class TemplateMakeCommand extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(TemplateScaffoldService $scaffold): int
    {
        $slug = (string) $this->argument('slug');
        $types = $this->option('type');
        $types = is_array($types) ? array_values($types) : [];
        $types = collect($types)
            ->flatMap(fn (string $value) => explode(',', $value))
            ->map(trim(...))
            ->filter()
            ->values()
            ->all();
        $css = (bool) $this->option('css');

        if ($types === []) {
            $this->error('Debe indicar --type=landing,dashboard,... como mínimo.');

            return self::FAILURE;
        }

        try {
            $home = $scaffold->make(slug: $slug, types: $types, name: str($slug)->headline()->toString(), withCss: $css);
        } catch (\RuntimeException $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        }

        $this->info("Tema [{$slug}] creado en {$home}.");

        return self::SUCCESS;
    }
}
