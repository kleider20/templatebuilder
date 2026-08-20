#!/usr/bin/env php
<?php

/**
 * Instalador del tema «{{ name }}» v{{ version }} (slug: {{ slug }}).
 *
 * Uso: extraé este ZIP en la raíz de un proyecto del Template Builder y
 * ejecutá:  php install.php
 */
$theme = '{{ slug }}';
$name = '{{ name }}';
$version = '{{ version }}';

function installFail(string $message): never
{
    fwrite(STDERR, "[ERROR] {$message}".PHP_EOL);
    exit(1);
}

function recursiveCopy(string $src, string $dst): void
{
    if (! is_dir($src)) {
        installFail("No se encontró la carpeta del tema: {$src}");
    }

    if (! is_dir($dst)) {
        mkdir($dst, 0755, true);
    }

    foreach (scandir($src) as $entry) {
        if ($entry === '.' || $entry === '..') {
            continue;
        }

        $from = $src.DIRECTORY_SEPARATOR.$entry;
        $to = $dst.DIRECTORY_SEPARATOR.$entry;

        if (is_dir($from)) {
            recursiveCopy($from, $to);
        } else {
            copy($from, $to);
        }
    }
}

// 1. Detectar la raíz del proyecto (artisan + resources/js/layouts).
$root = getcwd();

if (! is_file($root.DIRECTORY_SEPARATOR.'artisan') || ! is_dir($root.DIRECTORY_SEPARATOR.'resources'.DIRECTORY_SEPARATOR.'js'.DIRECTORY_SEPARATOR.'layouts')) {
    installFail('Ejecutá este script desde la raíz de un proyecto del Template Builder (debe tener artisan y resources/js/layouts).');
}

// 2. Comprobar requisitos.
if (PHP_VERSION_ID < 80300) {
    installFail('Se requiere PHP >= 8.3.');
}

// 3. Copiar el tema a resources/js/layouts/{slug}.
$src = __DIR__.DIRECTORY_SEPARATOR.$theme;
$dst = $root.DIRECTORY_SEPARATOR.'resources'.DIRECTORY_SEPARATOR.'js'.DIRECTORY_SEPARATOR.'layouts'.DIRECTORY_SEPARATOR.$theme;

if (is_dir($dst)) {
    echo "[AVISO] El tema «{$theme}» ya está instalado en resources/js/layouts/{$theme}.".PHP_EOL;
    echo '¿Sobrescribir? (s/N): ';
    $answer = trim((string) fgets(STDIN));

    if (! in_array(strtolower($answer), ['s', 'si', 'y', 'yes'], true)) {
        echo 'Instalación cancelada.'.PHP_EOL;
        exit(0);
    }
}

recursiveCopy($src, $dst);
echo "Tema copiado a resources/js/layouts/{$theme}.".PHP_EOL;

// 4. Validar y registrar con la app (si existe el comando).
$artisan = $root.DIRECTORY_SEPARATOR.'artisan';

if (is_file($artisan)) {
    echo 'Validando el tema con la aplicación…'.PHP_EOL;
    passthru('php "'.addslashes($artisan).'" template:import '.escapeshellarg($theme), $exitCode);

    if ($exitCode !== 0) {
        echo '[AVISO] La validación con la app falló, pero los archivos ya se copiaron.'.PHP_EOL;
    }
} else {
    echo '[AVISO] No se encontró artisan; copiá el tema manualmente y registralo luego.'.PHP_EOL;
}

// 5. Siguientes pasos.
echo PHP_EOL.'✅ Tema «'.$name.'» v'.$version.' instalado.'.PHP_EOL;
echo 'Siguientes pasos:'.PHP_EOL;
echo '  - Abrí /templates en tu proyecto para ver el tema en el catálogo.'.PHP_EOL;
echo '  - Si el tema no trae dist/ compilado: node bin/build-template.mjs '.$theme.PHP_EOL;
