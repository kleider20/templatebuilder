#!/usr/bin/env node
/**
 * Construye los artefactos dist/{slug}.js y dist/{slug}.css de un tema.
 *
 * Uso: node bin/build-template.mjs <slug> [--surface=landing] [--css-entry=path]
 *
 * - dist/{slug}.js  : entry virtual esbuild que importa TODOS los archivos de
 *                      components del manifest y exporta el mapa anidado
 *                      components = { superficie: { rol: Componente } }.
 *                      React se mantiene EXTERNO (import map compartido).
 * - dist/{slug}.css : Tailwind v4 compilado sobre los TSX del tema, con el
 *                      mapeo de roles a @theme inline y variante dark.
 */
import { execFileSync } from 'node:child_process';
import { mkdirSync, readFileSync, rmSync, writeFileSync } from 'node:fs';
import { dirname, join, relative, resolve } from 'node:path';
import { fileURLToPath } from 'node:url';
import { build as esbuild } from 'esbuild';

const __dirname = dirname(fileURLToPath(import.meta.url));
const ROOT = resolve(__dirname, '..');
const LAYOUTS = join(ROOT, 'resources', 'js', 'layouts');

const slug = process.argv[2];
const surfaceArg = process.argv.find((a) => a.startsWith('--surface='))?.split('=')[1];
const cssEntryArg = process.argv.find((a) => a.startsWith('--css-entry='))?.split('=')[1];

if (!slug) {
    console.error('Uso: node bin/build-template.mjs <slug> [--surface=landing] [--css-entry=path]');
    process.exit(1);
}

// --- 1. Localizar el home del tema vía template.json (descubrimiento) ---
function findManifest() {
    const surfaces = ['landing', 'dashboard', 'forms', 'auth'];

    for (const surface of surfaces) {
        const path = join(LAYOUTS, surface, 'templates', slug, 'template.json');

        if (exists(path)) {
            return { path, surface };
        }
    }

    return null;
}

function exists(p) {
    try {
        return readFileSync(p).length >= 0;
    } catch {
        return false;
    }
}

const manifestInfo = findManifest();

if (!manifestInfo) {
    console.error(`No se encontró template.json para el tema "${slug}".`);
    process.exit(1);
}

const manifestPath = manifestInfo.path;
const homeSurface = manifestInfo.surface;
const manifest = JSON.parse(readFileSync(manifestPath, 'utf8'));

const home = manifest.home || `${homeSurface}/templates/${slug}`;
const homeDir = join(LAYOUTS, ...home.split('/'));
const buildDir = join(ROOT, '.build');

// --- 2. Entry virtual de esbuild ---
const imports = [];
const mapLines = [];

for (const [surface, roles] of Object.entries(manifest.components || {})) {
    if (surfaceArg && surface !== surfaceArg) {
        continue;
    }

    const surfaceMap = [];

    for (const [role, relPath] of Object.entries(roles)) {
        const abs = resolve(LAYOUTS, relPath);
        const id = `${surface}_${role}`.replace(/[^a-zA-Z0-9_]/g, '_');
        imports.push(`import ${id} from ${JSON.stringify(abs)};`);
        surfaceMap.push(`${JSON.stringify(role)}: ${id}`);
    }

    mapLines.push(`${JSON.stringify(surface)}: { ${surfaceMap.join(', ')} }`);
}

const entryContent = [
    ...imports,
    `export const components = { ${mapLines.join(', ')} };`,
    'export default components;',
    '',
].join('\n');

const entryPath = join(buildDir, `${slug}-entry.tsx`);
mkdirSync(buildDir, { recursive: true });
writeFileSync(entryPath, entryContent);

const distDir = join(homeDir, 'dist');
mkdirSync(distDir, { recursive: true });

// --- 3. Compilar JS ---
const jsOut = join(distDir, `${slug}.js`);

try {
    await esbuild({
        entryPoints: [entryPath],
        outfile: jsOut,
        bundle: true,
        format: 'esm',
        minify: true,
        jsx: 'automatic',
        loader: { '.png': 'dataurl', '.svg': 'dataurl' },
        external: ['react', 'react-dom', 'react/jsx-runtime', 'react-dom/client'],
        logLevel: 'error',
    });
    console.log(`Bundle JS → ${relative(ROOT, jsOut)}`);
} finally {
    rmSync(entryPath, { force: true });
}

// --- 4. CSS: entry temporal + Tailwind CLI ---
const cssEntry = cssEntryArg ? resolve(cssEntryArg) : join(buildDir, `${slug}-css-entry.css`);

writeFileSync(
    cssEntry,
    cssEntryArg
        ? readFileSync(cssEntry, 'utf8')
        : buildCssEntry(slug, homeDir, manifest),
);

const cssOut = join(distDir, `${slug}.css`);
const tailwindCli = join(ROOT, 'node_modules', '@tailwindcss', 'cli', 'dist', 'index.mjs');

try {
    execFileSync(
        process.execPath,
        [tailwindCli, '--input', cssEntry, '--output', cssOut],
        { cwd: ROOT, stdio: 'inherit' },
    );
    console.log(`Bundle CSS → ${relative(ROOT, cssOut)}`);
} finally {
    if (!cssEntryArg) {
        rmSync(cssEntry, { force: true });
    }
}

/**
 * Construye el CSS entry por defecto del tema.
 */
function buildCssEntry(slugName, themeHome, m) {
    const roles = m.theme?.roles || ['primary', 'secondary', 'accent', 'background', 'surface', 'text'];
    const roleMap = roles.map((role) => `    --color-${role}: var(--tpl-${role});`).join('\n');

    const customCss = `${slugName}.css`;
    const customImport = exists(join(themeHome, customCss))
        ? `\n/* CSS custom del tema */\n@import ${JSON.stringify(customImportPath(themeHome, customCss))};\n`
        : '';

    return `@import "tailwindcss";

@custom-variant dark (&:where(.dark, .dark *));

@theme inline {
${roleMap}
}
${customImport}
`;
}

/**
 * Path relativo desde .build/ (donde vive el entry) hasta el CSS custom del home.
 */
function customImportPath(themeHome, customCss) {
    const entryDir = join(ROOT, '.build');
    const target = join(themeHome, customCss);
    const rel = relative(entryDir, target).replaceAll('\\', '/');

    return rel.startsWith('.') ? rel : `./${rel}`;
}
