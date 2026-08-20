<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\Templates\TemplateImportService;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use RuntimeException;

#[Signature('template:import {source} {--force} {--user=}')]
#[Description('Importa un tema del template builder desde un ZIP o un slug ya extraído')]
class TemplateImportCommand extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(TemplateImportService $import): int
    {
        $source = (string) $this->argument('source');
        $force = (bool) $this->option('force');

        $userOption = $this->option('user');
        $userId = $userOption !== null ? (int) $userOption : null;

        if ($userId !== null && ! User::whereKey($userId)->exists()) {
            $this->error('El usuario indicado en --user no existe.');

            return self::FAILURE;
        }

        try {
            if (is_file($source) && str_ends_with(strtolower($source), '.zip')) {
                $manifest = $import->importFromZip($source, $force, $userId);
            } else {
                $manifest = $import->importFromSlug($source, $userId);
            }
        } catch (RuntimeException $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        }

        $this->info("Tema [{$manifest->name}] (slug {$manifest->slug}) v{$manifest->version} importado en resources/js/layouts/{$manifest->home}.");

        return self::SUCCESS;
    }
}
