# Template Builder (MVP)

Builder de temas multi-superficie para Laravel 13 + React 19 (Inertia v3,
Tailwind CSS v4, Pest v4). Permite crear, editar, previsualizar y exportar
temas con un sistema de paletas/tokens y consumo "hot" en proyectos finales.

## Estructura de authoring

```
resources/js/layouts/
├── default/                      ← temas base INTERNOS (NO exportables)
│   ├── landing-default-template.tsx
│   ├── dashboard-default-template.tsx
│   ├── forms-default-template.tsx
│   ├── auth-default-template.tsx
│   └── components/{superficie}/  ← layouts/roles: {tipo}-default-{rol}.tsx
├── aurora/                       ← tema de usuario (HOME = "aurora")
│   ├── template.json             ← manifiesto (HOME del tema)
│   ├── landing-aurora-template.tsx  ← templates en la raíz
│   ├── dashboard-aurora-template.tsx
│   ├── forms-aurora-template.tsx
│   ├── auth-aurora-template.tsx
│   ├── components/{superficie}/  ← layouts/roles: {tipo}-{slug}-{rol}.tsx
│   ├── assets/ thumbnails/ dist/ ← solo en el HOME
│   └── aurora.css                ← CSS custom opcional (convención)
└── (wrappers del sistema: app/, auth/, settings/, app-layout.tsx, auth-layout.tsx)
```

- **Superficies:** `landing | dashboard | forms | auth`. Cada una declara su
  vocabulario de roles en `config/templates.php`:
  - landing: `template, layout, header, footer, topmenu`
  - dashboard: `template, layout, sidebar, topmenu`
  - forms: `template, layout, header, footer`
  - auth: `template, layout, login, logout`
- **Tema = carpeta hermana en `layouts/{slug}/`** con su `template.json` en la
  raíz. El **home** es esa carpeta y es la única que contiene el manifiesto,
  `assets/`, `thumbnails/` y `dist/`. Templates `{tipo}-{slug}-template.tsx` en
  la raíz y layouts/roles en `components/{tipo}/{tipo}-{slug}-{rol}.tsx`.
- `default/` es el tema base interno: no aparece en el catálogo ni en los
  exports; sirve de fallback y de referencia de scaffolds.
- Descubrimiento: escaneo de `layouts/*/template.json` (server + glob
  frontend). No hay slugs hardcodeados.

## Manifiesto `template.json`

Campos clave: `name`, `slug`, `type[]`, `version` (semver), `author`,
`description`, `thumbnail`, `home`, `components{superficie: {rol: path}}`,
`theme{roles, default, palettes[]}`, `settings{}`, `dependencies`,
`requires`, `exports{bundle, styles}`.

- Paths de `components` relativos a `resources/js/layouts/`.
- Paths de `exports` y `thumbnail` relativos al home.
- **theme es opcional** (compatibilidad): si falta, el runtime aplica el
  `fallback_palette` de `config/templates.php` (zinc + dark).
- Validaciones en `TemplateManifestService` (sección 3 del contrato):
  superficie/rol fuera de vocabulario, paths inexistentes, JSON roto (mensaje
  del parser), `theme.roles ⊇ [primary, secondary, accent]`,
  `count(palettes) >= min_palettes` (3), ids únicos y `default` existente,
  paleta con rol faltante → fallback zinc (no error).

## CSS: dos artefactos distintos (no mezclar)

1. **CSS de la app del builder** — generado por `npm run dev/build` (Tailwind v4
   escanea todos los TSX). Sirve solo al editor/preview del builder. Nunca va
   al ZIP.
2. **`dist/{slug}.css`** — artefacto POR TEMA generado al exportar (vía
   `bin/build-template.mjs`). Autocontenido: preflight + utilidades del tema +
   variante dark + tokens del theme + CSS custom opcional. Es el que viaja en
   el ZIP y cargan los proyectos finales sin compilar nada.

No existen `theme.css` ni `entry.tsx`. CSS custom opcional por convención:
si existe `{home}/{slug}.css` (sin `@import "tailwindcss"`), el export lo
fusiona en `dist/{slug}.css`.

## Sistema de temas (roles + paletas + tokens)

- Los roles de `theme.roles` se mapean a tokens de Tailwind v4 en el build con
  `@theme inline { --color-primary: var(--tpl-primary); ... }`. Los TSX usan
  clases semánticas: `bg-primary`, `text-content`, `bg-surface`, `ring-accent`.
- **Aplicación runtime:** el host mezcla la paleta elegida (default o
  selección del usuario) + overrides e inyecta las variables `--tpl-*` de
  `colors` como style inline en el wrapper `.tpl-root`, y las de dark mediante
  un `<style>` acotado (`.dark .tpl-root { ... }`).
- **Selección del usuario = `{ palette, overrides }`** persistida en la DB del
  CONSUMIDOR (nunca en el ZIP). En el preview del builder vive en
  estado/localStorage.
- GUI "Tema": grid de tarjetas de paleta (3 swatches primary/secondary/accent +
  label, ring al seleccionar) + pickers por rol (primary/secondary/accent) que
  generan overrides + botón "Restablecer".
- `settings` (no visuales: text/bool) se exponen en runtime vía
  `window.__TPL_SETTINGS__` (defaults del manifest mergeados) y se leen con un
  helper `useTemplateSettings()`.

## Assets

Los TSX NUNCA importan imágenes por bundler. URL canónica idéntica en builder,
sandbox y proyectos finales: `/templates/{slug}/assets/...`. Carpeta física en
`{home}/assets/`.

## Flujo de trabajo

- **Crear:** `php artisan template:make {slug} --type=landing,dashboard,... [--css]`
  o el formulario en `/templates/create` (comparten `TemplateScaffoldService`).
  Genera el home con manifiesto de 3 paletas (indigo/emerald/rose, default
  indigo), `assets/`, `thumbnails/`, TSX por superficie y `{slug}.css` solo con
  `--css`.
- **Editar:** `/templates/{slug}/editor` — árbol plano del tema (recorre el
  home una vez), `@monaco-editor/react`, Guardar (PUT) con Ctrl/Cmd+S e
  indicador de cambios. El botón "Manifiesto" edita los bloques `settings`/
  `theme` del `template.json` validando la sección 3.
- **Previsualizar:** `/templates/{slug}/preview` — selector de superficie
  (landing/dashboard/forms/login), toggle dark/light, GUI Tema (paletas +
  pickers + reset) y formulario de settings. Renderiza el tema desde su
  **código fuente** (`import.meta.glob` sobre `layouts/**/*.tsx`, indexado por
  `manifest.components`); en dev el `<link>` al CSS del tema lleva cache-busting
  (`?t=Date.now()`).
- **Exportar:** POST `/templates/{slug}/export` compila `dist/` y crea
  `output/{slug}-{version}.zip` (copia verbatim del home completo +
  `template.json`), registrando una `TemplateVersion` con sha256. El historial
  y la descarga viven en `/templates/versions`.

## Contrato de consumo "hot" (en el consumidor)

El consumidor instala el ZIP y, **una sola vez** (no por template), configura:

1. Copiar el ZIP en su proyecto: `{home}/**` (verbatim, incluye `template.json`
   y todos los `components/`) → `resources/js/layouts/`; `{home}/assets/**` y
   `thumbnails/` → `public/templates/{slug}/assets/`; `{home}/dist/**` →
   `public/templates/{slug}/dist/`.
2. Insertar `template.json` en la tabla `installed_templates`
   (slug, version, manifest, active).
3. `TemplateHost`: `<link>` a `/templates/{slug}/dist/{slug}.css` +
   `const mod = await import('/templates/${slug}/dist/${slug}.js')` y renderiza
   `mod.components[superficie][rol]`; aplica paleta y settings.
4. **Setup único:** Vite con los mismos externals
   (`react`, `react-dom`, `react/jsx-runtime`, `react-dom/client`) + import map
   a `/vendor/react/index.js` (React ESM compartido). Sin `npm run build` por
   template ni reiniciar el servicio.

El builder genera el artefacto ESM de React con
`node bin/build-vendor-react.mjs` (`npm run build:vendor`) y lo sirve en
`/vendor/react/index.js`, con el import map en `resources/views/app.blade.php`.

## Dark / Light

Todo componente usa variantes `dark:` y/o tokens del theme (que incluyen
overrides dark por paleta). `dist/{slug}.css` incluye
`@custom-variant dark (&:where(.dark, .dark *));`. El preview sincroniza `.dark`
con el host.

## Convenciones

- kebab-case en `.tsx/.ts`; default export en archivos de rol; named exports en
  internos; páginas Inertia default export.
- Colores SOLO vía tokens del theme (clases semánticas) o variables `--tpl-*`.
- TypeScript estricto; UI en español; estética zinc/indigo.
- Tras crear/modificar controllers: `php artisan wayfinder:generate --with-form`
  y usar actions/routes generados desde React.
- Pint y Pest verdes: `vendor/bin/pint` y `php artisan test --compact`.
