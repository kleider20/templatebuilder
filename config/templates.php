<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Directorio base de authoring
    |--------------------------------------------------------------------------
    | Carpeta raíz donde viven las superficies (landing, dashboard, forms, auth).
    | Los paths de los manifiestos son relativos a este directorio.
    */
    'base_path' => 'resources/js/layouts',

    /*
    |--------------------------------------------------------------------------
    | Superficies soportadas
    |--------------------------------------------------------------------------
    | Vocabulario de superficies y los roles de componente obligatorios por
    | superficie. "template" y "layout" son obligatorios siempre.
    */
    'surfaces' => [
        'landing' => ['template', 'layout', 'header', 'footer', 'topmenu'],
        'dashboard' => ['template', 'layout', 'sidebar', 'topmenu'],
        'forms' => ['template', 'layout', 'header', 'footer'],
        'auth' => ['template', 'layout', 'login', 'logout'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Temas default (internos, no exportables)
    |--------------------------------------------------------------------------
    | Tema base por superficie bajo {superficie}/default/ (NO exportable, no
    | aparece en el catálogo). Sirve de fallback y base de scaffolds.
    | login/logout viven en auth/default/.
    */
    'defaults' => [
        'landing' => 'landing/default/welcome-layout',
        'dashboard' => 'dashboard/default/dashboard-layout',
        'forms' => 'forms/default/forms-layout',
        'auth' => 'auth/default/auth-layout',
        'login' => 'auth/default/login',
        'logout' => 'auth/default/logout',
    ],

    /*
    |--------------------------------------------------------------------------
    | Sistema de temas
    |--------------------------------------------------------------------------
    */
    'theme_roles' => ['primary', 'secondary', 'accent', 'background', 'surface', 'text'],

    'min_palettes' => 3,

    'fallback_palette' => [
        'id' => 'zinc',
        'label' => 'Zinc',
        'colors' => [
            'primary' => '#18181b',
            'secondary' => '#52525b',
            'accent' => '#3f3f46',
            'background' => '#ffffff',
            'surface' => '#f4f4f5',
            'text' => '#18181b',
        ],
        'dark' => [
            'primary' => '#f4f4f5',
            'background' => '#09090b',
            'surface' => '#18181b',
            'text' => '#f4f4f5',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Rutas de paridad de URLs
    |--------------------------------------------------------------------------
    | Prefijos con los que se sirven los artefactos desde el "home" del tema.
    */
    'url_prefix' => 'templates',

    /*
    |--------------------------------------------------------------------------
    | Output
    |--------------------------------------------------------------------------
    | Carpeta destino de los ZIPs de export (relativa a la raíz del proyecto).
    */
    'output_path' => 'output',

    /*
    |--------------------------------------------------------------------------
    | Seguridad del editor
    |--------------------------------------------------------------------------
    | Extensiones permitidas para edición de archivos dentro de layouts/.
    */
    'editor_extensions' => ['tsx', 'ts', 'json', 'css'],
];
