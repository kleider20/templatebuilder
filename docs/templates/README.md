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
- **Temas default**: viven en `resources/js/layouts/{superficie}/default/` (`welcome-layout`/`welcome-template` en landing, `dashboard-layout`/`dashboard-template`, `forms-layout`/`forms-template`, `auth-layout`/`auth-template` + `login`/`logout`). Son el fallback y la base de scaffolds; **no exportables**.
- **Temas de usuario**: creados con `php artisan templates:make` (`TemplateMakeCommand`), scaffold vía `TemplateScaffoldService`, se guardan en `resources/js/layouts/{superficie}/{slug}/`.
- **Manifiesto**: archivo JSON por tema validado por `TemplateManifestService`; editado desde el editor (`manifest-editor.tsx`) y vía `templates.manifest.update`.
- **Preview**: `template-preview-surface.tsx` renderiza el tema en una superficie; carga el bundle `dist/{slug}.js` con URL absoluta de `window.location.origin` (funciona también en dev, donde el módulo corre desde el Vite dev server) e inyecta el `<link>` al CSS del tema (`manifest.exports.styles` → `dist/{slug}.css`), necesario para las utilidades y paletas del tema. Los ajustes se comparten con el hook `use-template-settings` y el evento `tpl:settings-changed`.
- **Thumbnail (preview)**: cada tema puede subir su imagen desde el catálogo (botón "Subir preview") vía `POST templates/{slug}/thumbnail`; se guarda como `{home}/thumbnails/preview.<ext>` y actualiza `thumbnail` en `template.json`. Si no existe archivo, el catálogo muestra `PlaceholderPattern`. Las URLs canónicas usan `templates/{slug}/thumbnails/{file}`.
- **Export**: `TemplateExportService` genera ZIP + registro `TemplateVersion`; las URLs canónicas de artefactos usan paridad (`templates/{slug}/dist|assets|thumbnails`).
- **Build**: `node bin/build-template.mjs {slug}` compila el tema a `dist/` (externaliza react/react-dom vía el bundle ESM `public/vendor/react/index.js` generado con `npm run build:vendor`).
- **Páginas frontend**: `resources/js/pages/templates/{index,create,editor,preview,versions}.tsx`.

## Terminología (sistema de layouts)

- **Wrapper** — layout público que se importa en `app.tsx` (`AppLayout`, `AuthLayout`) y expone un template concreto.
- **Template** — layout concreto bajo `layouts/{app,auth}/...` (`app-sidebar-layout.tsx`, `auth-simple-layout.tsx`) que arma la estructura visual.
- **Composición** — para `settings/*` se apilan dos layouts: `[AppLayout, SettingsLayout]`.

## Mapa de decisiones (páginas del sistema)

| Página Inertia | Layout aplicado | Template usado |
|----------------|-----------------|----------------|
| `welcome` | (ninguno) | — |
| `auth/*` | `AuthLayout` | `auth-simple-layout` |
| `settings/*` | `AppLayout` + `SettingsLayout` | `app-sidebar-layout` + sub-nav |
| `templates/*` (builder) | `AppLayout` (default) | `app-sidebar-layout` |
| resto (dashboard, etc.) | `AppLayout` | `app-sidebar-layout` |

## Reglas de mantenimiento

- Para cambiar la estructura global de las páginas autenticadas, editar `layouts/app/app-sidebar-layout.tsx` (o apuntar `app-layout.tsx` a otro template).
- Para cambiar la estructura de auth, editar `layouts/auth/auth-simple-layout.tsx` (o apuntar `auth-layout.tsx` a `auth-card-layout`/`auth-split-layout`).
- Los **temas default** por superficie (`layouts/{superficie}/default/`) se mantienen al día con `config/templates.php::defaults`.
- Todo archivo de `resources/js/` debe seguir **kebab-case**.
- No crear nuevas carpetas base de layouts sin aprobación.

## Vínculos

- Vista general de layouts → [`../design/layouts.md`](../design/layouts.md)
- Arquitectura frontend → [`../architecture/frontend.md`](../architecture/frontend.md)