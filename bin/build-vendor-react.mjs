#!/usr/bin/env node
/**
 * Construye el artefacto ESM de React para el import map del host.
 *
 * El bundle de un tema externaliza react/react-dom/react/jsx-runtime y se
 * carga en el navegador con bare specifiers. El host los resuelve vía un
 * import map que apunta los cuatro specifiers al MISMO archivo, garantizando
 * una instancia única de React compartida (hooks + jsx-runtime + react-dom).
 *
 * Uso: node bin/build-vendor-react.mjs
 *
 * Salida: public/vendor/react/index.js
 */
import { mkdirSync } from 'node:fs';
import { dirname, join, resolve } from 'node:path';
import { fileURLToPath } from 'node:url';
import { build as esbuild } from 'esbuild';

const __dirname = dirname(fileURLToPath(import.meta.url));
const ROOT = resolve(__dirname, '..');
const OUT = join(ROOT, 'public', 'vendor', 'react');
const ENTRY_DIR = join(ROOT, '.build', 'vendor-react');

mkdirSync(OUT, { recursive: true });

await esbuild({
    entryPoints: [join(ENTRY_DIR, 'full-entry.tsx')],
    bundle: true,
    format: 'esm',
    outfile: join(OUT, 'index.js'),
    minify: true,
    jsx: 'automatic',
    logLevel: 'error',
});

console.log('Vendor React → /vendor/react/index.js');
