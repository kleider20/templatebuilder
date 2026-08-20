# Sistema de Templates de UI y Template Builder

Este directorio documenta dos cosas relacionadas:

1. El **sistema de templates/layouts** de la aplicación React/Inertia: los "moldes" que envuelven las páginas (`app-layout`, `auth-layout`, `settings/layout`, templates de app/auth).
2. El **template builder** de producto (CONTRATO v7): editor/CRUD de plantillas, superficies, temas default, manifiestos, preview y exportación con paridad de URLs.

## Inventario de archivos

```
docs/templates/
├── README.md            ← este índice
├── root-layouts.md      ← app-layout, auth-layout, settings/layout + despachador app.tsx
├── app-templates.md     ← app-shell, sidebar-layout, header-layout, AppContent/Sidebar
├── auth-templates.md    ← auth-simple (activo), auth-card, auth-split (no usados)
└── nomenclature.md      ← jerarquía Wrapper/Template y convenciones de nombres
```

## Template builder (dominio de producto)

- **Config**: `config/templates.php` — `base_path` (`resources/js/layouts`), `surfaces` (landing/dashboard/forms/auth), `defaults` (temas internos no exportables), `theme_roles`, `min_palettes`, `fallback_palette`, `url_prefix`, `output_path`, `editor_extensions`.
- **Superficies y roles obligatorios**:
  - `landing` → `template`, `layout`, `header`, `footer`, `topmenu`
  - `dashboard` → `template`, `layout`, `sidebar`, `topmenu`
  - `forms` → `template`, `layout`, `header`, `footer`
  - `auth` → `template`, `layout`, `login`, `logout`
- **Temas default**: viven consolidados en `resources/js/layouts/default/` con el mismo naming que los temas (`landing-default-template.tsx`, `dashboard-default-template.tsx`, `forms-default-template.tsx`, `auth-default-template.tsx`; layouts/roles en `default/components/{superficie}/{tipo}-default-{rol}.tsx`). Son el fallback y la base de scaffolds; **no exportables**.
- **Temas de usuario**: creados con `php artisan template:make` (`TemplateMakeCommand`), scaffold vía `TemplateScaffoldService`. Cada tema es una **carpeta hermana en `resources/js/layouts/{slug}/`** con su `template.json` en la raíz: templates `{tipo}-{slug}-template.tsx` en la raíz y layouts/roles en `components/{tipo}/{tipo}-{slug}-{rol}.tsx`.
- **Manifiesto**: archivo JSON por tema validado por `TemplateManifestService`; editado desde el editor (`manifest-editor.tsx`) y vía `templates.manifest.update`. El bloque `pricing` define la comercialización: `{type: 'free'|'premium', price: number|null, currency: 'USD'}`. Los temas default son **siempre gratis** (`TemplateScaffoldService` los genera con `type: free`); un tema `premium` exige `price > 0`. `CatalogTemplatesAction` expone el pricing para el catálogo y el marketplace.
- **Marketplace** (público, sin login): `GET /marketplace` (`TemplateMarketplaceController` + `MarketplaceTemplatesAction`) lista los temas **default + registrados** con su pricing; la página `resources/js/pages/templates/marketplace.tsx` muestra badge FREE (verde) o precio (US$), y `GET /marketplace/{slug}/download` devuelve el ZIP de export (404 si el tema no tiene `TemplateVersion`). El dispatch de `app.tsx` devuelve `null` para esta página (sin chrome del builder).
- **Distribución / instalación**: el ZIP de export (`TemplateExportService`) incluye `template.json`, la carpeta del tema con `dist/` y dos archivos comerciales renderizados desde `resources/stubs/commercial/`: `install.php` (instalador CLI que copia el tema a `resources/js/layouts/{slug}` y corre `php artisan template:import {slug}`) y `README.md` (guía del tema, usa el pricing para mostrar "Gratis" o el precio). `TemplateImportService` + `php artisan template:import {source|--force|--user}` re-importan un tema: desde ZIP lo extrae en `storage/app/template-imports`, valida el manifiesto contra una base temporal y copia al home real (falla con mensaje si ya existe salvo `--force`); desde un slug ya extraído valida el manifiesto. Registra la propiedad en la tabla `templates` **solo si** se pasa `--user` (columna `user_id` NOT NULL con FK), y recompila `dist/` con `node bin/build-template.mjs {slug}` si el ZIP no trae artefactos.
- **Preview**: `template-preview-surface.tsx` renderiza el tema en una superficie **a partir del código fuente** (rutas de `manifest.components`) usando `import.meta.glob('/resources/js/layouts/**/*.tsx')` indexado por la ruta del rol; Vite los compila en dev y los agrupa en chunks en el build. No depende del bundle `dist/{slug}.js` ni del import map. Los componentes `lazy` se construyen **una sola vez a nivel de módulo** (por ruta): crearlos dentro del render (aunque fuera en un `useMemo`) cambia su identidad entre renders y, con React Compiler + StrictMode, el `Suspense` queda atascado en "Cargando superficie…" para siempre. Se mantiene el `<link>` al CSS del tema (`manifest.exports.styles` → `dist/{slug}.css`), necesario para las utilidades y paletas del tema (`bg-primary`, `bg-surface`, `text-content`, `--tpl-*`). Un `ErrorBoundary` envuelve al `Suspense` (un fallo de import muestra error en vez de quedar en "Cargando superficie…"). Los ajustes se comparten con el hook `use-template-settings` y el evento `tpl:settings-changed`.
  - **Página full-screen**: `templates/preview` se renderiza **sin el chrome del builder** (`app.tsx` devuelve `null`). El template del tema ocupa toda la pantalla y los controles (pestañas de superficie landing/dashboard/forms/auth, apariencia, paletas, overrides y opciones del tema) viven en un **panel flotante** arriba-derecha con enlace "← Plantillas" y botón "Ocultar/Mostrar controles". El `SurfaceBadge` se ubica arriba-izquierda.
  - **Composición por roles**: `DynamicTemplate` compone la superficie completa resolviendo todos los roles de `manifest.components[surface]` según el vocabulario de `config/templates.php` — `header`/`topmenu` arriba, `sidebar` al costado (dashboard), `template` como contenido principal (el `template` ya envuelve su `layout`) y `footer` abajo. En `auth` el `login` se renderiza como contenido del `template`. Cada pieza va envuelta en su propio `ErrorBoundary` para aislar fallos; si un rol falta se omite y si no hay `template`/`layout` se muestra `PreviewEmpty`. Un **badge flotante** (esquina superior) indica la superficie activa y un **placeholder sutil** dentro del `<main>` evita que el preview se vea vacío cuando el tema no aporta contenido.
- **Thumbnail (preview)**: cada tema puede subir su imagen desde el catálogo (botón "Subir preview") vía `POST templates/{slug}/thumbnail`; se guarda como `{home}/thumbnails/preview.<ext>` y actualiza `thumbnail` en `template.json`. Si no existe archivo, el catálogo muestra `PlaceholderPattern`. Las URLs canónicas usan `templates/{slug}/thumbnails/{file}`.
- **Export**: `TemplateExportService` genera ZIP + registro `TemplateVersion`; las URLs canónicas de artefactos usan paridad (`templates/{slug}/dist|assets|thumbnails`).
- **Build**: `node bin/build-template.mjs {slug}` compila el tema a `dist/` (externaliza react/react-dom vía el bundle ESM `public/vendor/react/index.js` generado con `npm run build:vendor`). El CSS entry (`buildCssEntry`) mapea los **roles** del tema y los **tokens semánticos shadcn** (`content`/`foreground`→`text`, `card`/`input`/`muted`→`surface`, `border`→`text` 15%, `muted-foreground`→`text` 60%, `primary-foreground`→`background`, `ring`→`primary`) a `--tpl-*`, de modo que `dist/{slug}.css` genera las utilidades que usan los componentes y queda **autocontenido**.
- **Páginas frontend**: `resources/js/pages/templates/{create,editor,preview,versions,marketplace}.tsx` (el catálogo `index` se eliminó; vive en `dashboard/plantillas`).

## Terminología (sistema de layouts)

- **Wrapper** — layout público que se importa en `app.tsx` (`AppLayout`, `AuthLayout`) y expone un template concreto.
- **Template** — layout concreto bajo `layouts/{app,auth}/...` (`app-sidebar-layout.tsx`, `auth-simple-layout.tsx`) que arma la estructura visual.
- **Composición** — para `settings/*` se apilan dos layouts: `[AppLayout, SettingsLayout]`.

## Mapa de decisiones (páginas del sistema)

| Página Inertia | Layout aplicado | Template usado |
|----------------|-----------------|----------------|
| `landing/welcome/welcome` | `LandingLayout` (surface) | template **landing** activo (hoy `landing-default-template`) |
| `forms/*` | `FormsLayout` (surface) | template **forms** activo (hoy `forms-default-template`) |
| `dashboard/*` | `DashboardLayout` (surface) | template **dashboard** del tema default (`dashboard-default-template` + sidebar/topbar) |
| `dashboard/plantillas` | `DashboardLayout` | catálogo **canónico** de plantillas (ex `templates/index`) |
| `auth/*` | `AuthLayout` | `auth-simple-layout` |
| `settings/*` | `AppLayout` + `SettingsLayout` | `app-sidebar-layout` + sub-nav |
| `templates/preview` | (ninguno) | preview full-screen del tema |
| `templates/marketplace` | (ninguno) | marketplace público sin chrome |
| `templates/*` (builder) | `AppLayout` (default) | `app-sidebar-layout` |
| resto | `AppLayout` | `app-sidebar-layout` |

> El catálogo de plantillas **ya no vive en `templates/index`** (ruta eliminada; `GET /templates` responde 405). Vive en `dashboard.plantillas` (`/dashboard/plantillas`) y se renderiza dentro del chrome del dashboard, con **tabs por tipo** (Todos/Landing/Dashboard/Auth/Forms según `surfaces`) combinados con el filtro de precios (Todos/Gratis/Premium). El marketplace sigue siendo público en `/marketplace` y las URLs de paridad de artefactos (`templates/{slug}/{dir}/{file}`) se conservan públicas e idénticas a los proyectos finales.

> Las páginas viven en `pages/{superficie}/{pagina}/{pagina}.tsx` y no definen layout de superficie; el dispatcher de `app.tsx` lo asigna por prefijo. B5 inyectará el **tema activo persistido** en los wrappers de superficie (`.ai/rules/pages.md`).

## Reglas de mantenimiento

- Para cambiar la estructura global de las páginas autenticadas, editar `layouts/app/app-sidebar-layout.tsx` (o apuntar `app-layout.tsx` a otro template).
- Para cambiar la estructura de auth, editar `layouts/auth/auth-simple-layout.tsx` (o apuntar `auth-layout.tsx` a `auth-card-layout`/`auth-split-layout`).
- Los **temas default** por superficie (`layouts/default/components/{superficie}/`) se mantienen al día con `config/templates.php::defaults`.
- Todo archivo de `resources/js/` debe seguir **kebab-case**.
- No crear nuevas carpetas base de layouts sin aprobación.

## Vínculos

- Vista general de layouts → [`../design/layouts.md`](../design/layouts.md)
- Arquitectura frontend → [`../architecture/frontend.md`](../architecture/frontend.md)