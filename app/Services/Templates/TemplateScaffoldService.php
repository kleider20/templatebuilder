<?php

namespace App\Services\Templates;

use Illuminate\Support\Facades\File;
use RuntimeException;

/**
 * Crea la estructura de un tema nuevo (home + superficies + manifiesto).
 * Lógica compartida entre el comando template:make y la página "crear".
 */
class TemplateScaffoldService
{
    public function __construct(
        protected TemplateManifestService $manifests,
    ) {}

    /**
     * Crea un tema nuevo.
     *
     * @param  array<int, string>  $types  superficies del tema
     * @param  string  $name  nombre legible
     * @param  bool  $withCss  si se genera {slug}.css custom
     *
     * @throws RuntimeException
     */
    public function make(
        string $slug,
        array $types,
        string $name,
        string $author = 'System',
        bool $withCss = false,
    ): string {
        if (! $this->isValidSlug($slug)) {
            throw new RuntimeException('El slug debe estar en kebab-case (solo letras minúsculas, números y guiones).');
        }

        $slug = str($slug)->slug()->toString();
        $types = array_values(array_intersect($types, $this->manifests->surfaces()));

        if ($slug === '') {
            throw new RuntimeException('El slug no es válido.');
        }

        if ($types === []) {
            throw new RuntimeException('Debe indicarse al menos una superficie válida.');
        }

        $types = array_values(array_unique($types));

        $home = $types[0].'/templates/'.$slug;
        $homeDir = $this->manifests->homePath($home);

        if (is_dir($homeDir)) {
            throw new RuntimeException("El tema [{$slug}] ya existe.");
        }

        $stubDir = resource_path('stubs/template');

        foreach ($types as $type) {
            $surfaceDir = $this->manifests->basePath().'/'.$type.'/templates/'.$slug;

            if ($type === $types[0]) {
                $this->makeHome($stubDir, $type, $slug, $surfaceDir);
            } else {
                $this->makeSurface($stubDir, $type, $slug, $surfaceDir);
            }
        }

        $this->writeManifest($stubDir, $slug, $name, $types, $home, $author, $withCss);

        return $home;
    }

    /**
     * Crea el home del tema: superficies de type[0] + assets/ + thumbnails/.
     */
    protected function makeHome(string $stubDir, string $type, string $slug, string $surfaceDir): void
    {
        File::makeDirectory($surfaceDir.'/assets', 0755, true);
        File::makeDirectory($surfaceDir.'/thumbnails', 0755, true);

        $this->makeSurface($stubDir, $type, $slug, $surfaceDir);
    }

    /**
     * Crea los archivos de una superficie (roles según vocabulario).
     */
    protected function makeSurface(string $stubDir, string $type, string $slug, string $surfaceDir): void
    {
        $componentsDir = $surfaceDir.'/components';

        File::makeDirectory($componentsDir, 0755, true);

        $roles = $this->manifests->vocabulary()[$type];

        foreach ($roles as $role) {
            $stub = $this->roleStubPath($stubDir, $type, $role);

            if ($stub === null) {
                continue;
            }

            $content = $this->renderStub($stub, [
                'slug' => $slug,
                'type' => $type,
                'stub_ucfirst' => ucfirst($slug),
            ]);

            if ($role === 'template' || $role === 'layout') {
                $path = $surfaceDir.'/'.$this->surfaceFile($type, $slug, $role);
            } else {
                $path = $componentsDir.'/'.$slug.'-'.$this->roleFile($role);
            }

            File::put($path, $content);
        }
    }

    /**
     * Nombre de archivo para template/layout según superficie.
     */
    protected function surfaceFile(string $type, string $slug, string $role): string
    {
        return $type === 'landing'
            ? $slug.'-'.($role === 'template' ? 'template' : 'layout').'.tsx'
            : $slug.'-'.$type.'-'.($role === 'template' ? 'template' : 'layout').'.tsx';
    }

    /**
     * Nombre de archivo de un rol secundario.
     */
    protected function roleFile(string $role): string
    {
        return match ($role) {
            'login' => 'login.tsx',
            'logout' => 'logout.tsx',
            default => $role.'.tsx',
        };
    }

    /**
     * Path del stub para un rol dado; los roles template/layout comparten stub base.
     */
    protected function roleStubPath(string $stubDir, string $type, string $role): ?string
    {
        $map = [
            'template' => $type === 'landing' ? 'template-landing.stub' : 'template.stub',
            'layout' => $type === 'landing' ? 'layout-landing.stub' : 'layout.stub',
            'header' => 'header.stub',
            'footer' => 'footer.stub',
            'topmenu' => 'topmenu.stub',
            'sidebar' => 'sidebar.stub',
            'login' => 'login.stub',
            'logout' => 'logout.stub',
        ];

        $name = $map[$role] ?? null;

        if ($name === null) {
            return null;
        }

        $path = $stubDir.'/'.$name;

        return File::exists($path) ? $path : null;
    }

    /**
     * Rellena los placeholders del stub.
     *
     * @param  array<string, string>  $vars
     */
    protected function renderStub(string $stubPath, array $vars): string
    {
        $content = File::get($stubPath);

        foreach ($vars as $key => $value) {
            $content = str_replace('{{ '.$key.' }}', $value, $content);
        }

        return $content;
    }

    /**
     * Escribe template.json.
     *
     * @param  array<int, string>  $types
     */
    protected function writeManifest(
        string $stubDir,
        string $slug,
        string $name,
        array $types,
        string $home,
        string $author,
        bool $withCss,
    ): void {
        $manifestFile = $this->manifests->homePath($home).'/template.json';
        $manifest = $this->buildManifest($slug, $name, $types, $home, $author);

        File::put($manifestFile, json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        if ($withCss) {
            File::put($this->manifests->homePath($home).'/'.$slug.'.css', $this->customCssStub($slug));
        }
    }

    /**
     * Construye el manifiesto completo del tema (theme de 3 paletas indigo/emerald/rose).
     *
     * @param  array<int, string>  $types
     * @return array<string, mixed>
     */
    protected function buildManifest(string $slug, string $name, array $types, string $home, string $author): array
    {
        $components = [];

        foreach ($types as $type) {
            $components[$type] = $this->surfaceComponents($type, $slug);
        }

        return [
            'name' => $name,
            'slug' => $slug,
            'type' => $types,
            'version' => '1.0.0',
            'author' => $author,
            'description' => "Tema [{$name}] multi-superficie.",
            'thumbnail' => 'thumbnails/'.$slug.'.png',
            'home' => $home,
            'components' => $components,
            'theme' => [
                'roles' => config('templates.theme_roles'),
                'default' => 'indigo',
                'palettes' => [
                    $this->indigoPalette(),
                    $this->emeraldPalette(),
                    $this->rosePalette(),
                ],
            ],
            'settings' => [
                'hero_title' => ['type' => 'text', 'label' => 'Título del hero', 'default' => 'Bienvenido'],
                'show_topmenu' => ['type' => 'bool', 'label' => 'Mostrar top menu', 'default' => true],
            ],
            'dependencies' => [],
            'requires' => [
                'php' => '>=8.3',
                'laravel' => '>=13',
                'react' => '>=19',
                'tailwind' => '>=4',
            ],
            'exports' => [
                'bundle' => 'dist/'.$slug.'.js',
                'styles' => 'dist/'.$slug.'.css',
            ],
        ];
    }

    /**
     * Componentes de una superficie en el manifiesto.
     *
     * @return array<string, string>
     */
    protected function surfaceComponents(string $type, string $slug): array
    {
        $roles = $this->manifests->vocabulary()[$type];

        $map = [];

        foreach ($roles as $role) {
            $map[$role] = match ($role) {
                'template' => $type === 'landing'
                    ? "{$type}/templates/{$slug}/{$slug}-template.tsx"
                    : "{$type}/templates/{$slug}/{$slug}-{$type}-template.tsx",
                'layout' => $type === 'landing'
                    ? "{$type}/templates/{$slug}/{$slug}-layout.tsx"
                    : "{$type}/templates/{$slug}/{$slug}-{$type}-layout.tsx",
                'login' => "{$type}/templates/{$slug}/components/{$slug}-login.tsx",
                'logout' => "{$type}/templates/{$slug}/components/{$slug}-logout.tsx",
                default => "{$type}/templates/{$slug}/components/{$slug}-{$role}.tsx",
            };
        }

        return $map;
    }

    /**
     * @return array<string, mixed>
     */
    protected function indigoPalette(): array
    {
        return [
            'id' => 'indigo', 'label' => 'Índigo',
            'colors' => ['primary' => '#4f46e5', 'secondary' => '#818cf8', 'accent' => '#22d3ee', 'background' => '#ffffff', 'surface' => '#f4f4f5', 'text' => '#18181b'],
            'dark' => ['primary' => '#6366f1', 'background' => '#09090b', 'surface' => '#18181b', 'text' => '#f4f4f5'],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function emeraldPalette(): array
    {
        return [
            'id' => 'emerald', 'label' => 'Esmeralda',
            'colors' => ['primary' => '#059669', 'secondary' => '#34d399', 'accent' => '#f59e0b', 'background' => '#ffffff', 'surface' => '#f4f4f5', 'text' => '#18181b'],
            'dark' => ['primary' => '#10b981', 'background' => '#09090b', 'surface' => '#18181b', 'text' => '#f4f4f5'],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function rosePalette(): array
    {
        return [
            'id' => 'rose', 'label' => 'Rosa',
            'colors' => ['primary' => '#e11d48', 'secondary' => '#fb7185', 'accent' => '#8b5cf6', 'background' => '#ffffff', 'surface' => '#f4f4f5', 'text' => '#18181b'],
            'dark' => ['primary' => '#f43f5e', 'background' => '#09090b', 'surface' => '#18181b', 'text' => '#f4f4f5'],
        ];
    }

    /**
     * Stub de CSS custom opcional.
     */
    protected function customCssStub(string $slug): string
    {
        return "/* {$slug} custom css (sin @import 'tailwindcss'). */\n";
    }

    /**
     * Comprueba si un string es un slug kebab-case válido.
     */
    protected function isValidSlug(string $slug): bool
    {
        return (bool) preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $slug);
    }
}
