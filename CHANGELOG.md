# CHANGELOG

Registro de cambios **resueltos** en el proyecto, ordenados de más reciente a más antigua y agrupados por versión/fecha.

> **Regla de uso:** cada tarea que el asistente resuelva se agrega aquí con su fecha. Los pendientes van en [`TODO.md`](./TODO.md), **no** aquí.

Formato basado en [Keep a Changelog](https://keepachangelog.com/es-ES/1.0.0/) + [SemVer](https://semver.org/).

## [Sin publicar]

### Preview de temas a pantalla completa + CSS del tema autocontenido (2026-08-16)
- **El CSS del tema ahora es autocontenido**: `bin/build-template.mjs` → `buildCssEntry()` añade los **tokens semánticos shadcn** al `@theme inline` mapeados a los roles del tema (`content`/`foreground` → `text`, `card`/`input`/`muted` → `surface`, `border` → `text` al 15%, `muted-foreground` → `text` al 60%, `primary-foreground` → `background`, `ring` → `primary`). Esto genera utilidades que los componentes ya usaban (`text-content`, `border-border`, `bg-card`, `border-input`, `text-muted-foreground`) y que faltaban en `dist/{slug}.css`. `dist/aurora` regenerado.
- **Preview del tema a pantalla completa**: `app.tsx` devuelve `null` para `templates/preview` (sin el chrome del builder); `preview.tsx` aplica el template del tema a toda la página con **pestañas flotantes** para cambiar de superficie (landing/dashboard/forms/auth), panel de controles colapsable (apariencia, paletas, overrides, opciones del tema), enlace "← Plantillas" y botón "Ocultar/Mostrar controles". El `SurfaceBadge` pasa a la izquierda y el mensaje vacío se ajusta a "No existe un template para la superficie «X» en este tema."
- Nuevo test `tests/Feature/Templates/CssBuildTest.php` que verifica que el `dist/{slug}.css` mapea roles y tokens semánticos a `--tpl-*` y genera las utilidades (skip si el tema no está compilado).

### Reestructuración tema-first de los temas (2026-08-16)
- **Los temas ahora son carpetas hermanas en `resources/js/layouts/{slug}/`** (home = slug), ya no `{superficie}/templates/{slug}/`. Templates `{tipo}-{slug}-template.tsx` en la raíz y layouts/roles en `components/{tipo}/{tipo}-{slug}-{rol}.tsx`. `manifest.home` = slug.
- **Temas default consolidados** en `resources/js/layouts/default/` con naming `{tipo}-default-{rol}` (`landing-default-template.tsx`, `auth-default-login.tsx`, etc.); se eliminan las carpetas `{superficie}/default/` y `{superficie}/templates/`.
- `TemplateManifestService` (scan por `template.json` en dirs de base_path, validate `home === slug`), `TemplateScaffoldService` (home = slug, templates en raíz + `components/{tipo}/`), `TemplateExportService::zipVerbatim` (home completo + `template.json`), `ResolveTemplateFileAction`, `BuildEditorTreeAction` (recorre el home una vez) actualizados.
- Editor: `groupTreeBySection`/`describeSection` agrupan por tipo vía `components/{tipo}`. `bin/build-template.mjs` descubre el manifest en `layouts/{slug}/template.json`.
- `.gitignore` → `/resources/js/layouts/*/dist/`. Tests actualizados (ManifestTest/EditorTest/ExportTest). Suite 69 ok + 1 skip, tsc y lint en verde; `dist/aurora` regenerado.

### Fix preview de temas: render del código fuente (2026-08-14)
- **El preview ya no depende del bundle remoto `dist/{slug}.js`**: `template-preview-surface.tsx` renderiza la superficie desde el **código fuente** del tema. Resuelve la ruta del rol en `manifest.components` (p. ej. `landing/templates/aurora/aurora-template.tsx`) y la carga con `import.meta.glob('/resources/js/layouts/**/*.tsx')` indexado por `/resources/js/layouts/{path}`. Elimina la fragilidad del `import()` dinámico remoto (import map, MIME, bundle presente) que podía dejar la superficie en "Cargando superficie…" para siempre en dev.
- **`ErrorBoundary` por encima del `Suspense`**: un fallo del import ahora muestra "No se pudo cargar el template" en vez de quedar colgado en el fallback o romper la app. Se conserva el `ErrorBoundaryHOC` interno para errores de render del tema.
- Se mantiene el `<link>` al CSS del tema `dist/{slug}.css` (`manifest.exports.styles`) con cache-bust de dev, necesario para las utilidades/paletas (`bg-primary`, `--tpl-*`).
- `preview.tsx` pasa `components={manifest.components}` a `PreviewSurface`. El import map y `build:vendor` quedan para la app y el consumo real/sandbox de temas.

### Arquitectura en espejo + RBAC + Seguridad (2026-08-06)
- **Modularización** del dominio de Templates y Settings:
  - Controllers movidos a `app/Http/Controllers/Templates/` (namespace `App\Http\Controllers\Templates`); eliminados los controllers raíz obsoletos.
  - Lógica de negocio extraída a **Actions** (`app/Actions/Templates`, `app/Actions/Settings`); controllers quedan delgados.
  - Validación movida a **FormRequests** (`app/Http/Requests/Templates`, `app/Http/Requests/Settings`); nunca inline.
  - **Data objects** con `spatie/laravel-data` (`app/Data/Templates`, `app/Data/Settings`); sustituyen a `TemplateManifestDto`/`TemplateComponentDto` (eliminados).
- **RBAC** con `spatie/laravel-permission`:
  - Modelos `Role`, `Permission`, `RoleMetadata`, `PermissionMetadata`; tabla `templates` y columnas `is_test_user`/`is_undeletable` en `users`.
  - Roles: `super_admin`, `admin`, `editor`, `user`, `base`; `admin` con todos los permisos (pruebas), `super_admin` con `Gate::before` universal.
  - Seeders encadenados (`RolePermissionSeeder`, `RoleMetadataSeeder`, `PermissionMetadataSeeder`, `TestSuperAdminSeeder`).
  - Rol `user` por defecto al registrarse (`CreateNewUser`). Comando `user:promote`.
  - `TemplatePolicy` + registro en `AppServiceProvider` (`view_any/create/view/update/export/delete/viewAll`).
  - `HandleInertiaRequests` comparte `roles`/`permissions`.
- **Correcciones de seguridad**:
  - `Controller.php` base usa `AuthorizesRequests` (elimina 403 espurios por `authorize()` no definido).
  - `TemplatePreviewController@show` → `abort(404)` ante manifiesto ausente.
  - Eliminación de cuenta: `setRememberToken(null)` antes del borrado (evita re-in yección por `Auth::logout()`).
  - Registro tolerante a roles ausentes (`CreateNewUser` no rompe si no hay seeds).
  - `SecurityController` omite props 2FA cuando el feature está desactivado.
- **Rutas**: `routes/templates.php` (grupo auth+verified + paridad pública) y `routes/settings.php` (mantiene `profile.*`/`security.*`).
- Frontend: `resources/js/pages/templates/index.tsx` con `canCreate` condicional.
- **Lint JS en verde** (`npm run lint`, 0 errores):
  - `eslint.config.js`: excluidos bundles generados `resources/js/**/dist/**`, stubs `*.stub` y añadidos globals de Node para `bin/**/*.mjs`.
  - Limpieza de imports/vars sin uso en `pages/templates/editor.tsx` y `pages/templates/preview.tsx`.
- **Fix preview de temas**: el `import()` del bundle quedaba en "Cargando superficie…" en dev porque el `moduleUrl` relativo resolvía contra el Vite dev server (el módulo corre desde localhost:5173, pero los bundles están en `resources/js/layouts` y los sirve Laravel). Ahora usa `window.location.origin` absoluto (`template-preview-surface.tsx`). También se inyecta el `<link>` al CSS del tema `dist/{slug}.css` (antes jamás se cargaba → las utilidades `bg-primary`/`bg-surface` y las vars `--tpl-*` no existían y los temas/colores no se aplicaban).
- **Panel "Archivos" del editor**: agrupación por sección/superficie (primer segmento del pathname). Muestra el nombre de archivo, y en caso de duplicados dentro de la misma sección muestra `subcarpeta/nombre` para diferenciarlos (`groupTreeBySection` en `pages/templates/editor.tsx`).
  - `template-preview-surface.tsx`: eliminado `reloadKey` (setState-en-effect) usando `key={surface}` directo; cache-bust de DEV movido dentro del factory asíncrono del `lazy`; disables puntuales justificados para `react-hooks/purity` y `react-hooks/static-components` (carga remota dinámica por slug).
- Tests: suite completa en verde (69 ok, 1 skip preexistente). Helper `templateAdminUser()` en `tests/Pest.php`.
- **Corrección de bug en el editor**: el cambio de archivo no actualizaba el contenido (solo URL/etiqueta) porque se sincronizaba estado local con `useEffect` (`set-state-in-effect`, restringido en React 19). Se reestructuró en un componente hijo `FileEditor` con `key={activeFile?.path}` para remontar por archivo y `path` derivado del prop del servidor. Lint/tsc/build/tests verificados en verde.

### Añadido (2026-08-05)
- Cierre de brechas del contrato v7 del template-builder:
  - Ctrl/Cmd+S para guardar en el editor (`pages/templates/editor.tsx`).
  - Cache-busting (`?t=Date.now()`) en modo dev del preview de superficies.
  - Import map de React ESM compartido: `bin/build-vendor-react.mjs` genera
    `public/vendor/react/index.js` (instancia única de react/jsx-runtime/react-dom)
    y `resources/views/app.blade.php` lo expone como import map.
  - Tests de endpoints: 401 sin autenticación, paleta inexistente → 422,
    paleta válida, manifiesto inválido → 422 (`tests/Feature/Templates/EndpointsTest.php`).
  - Validación de `palette` en `TemplatePreviewController@data` (Rule::in).
  - UI "Manifiesto" en el editor (`components/manifest-editor.tsx`) para editar
    settings/theme del template.json con validación de la sección 3.
  - Temas `default/` internos por superficie (welcome/dashboard/forms/auth +
    login/logout) y `config/templates.php::defaults` apuntando a ellos.
  - Hook `useTemplateSettings`/`useTemplateSetting` +
    evento `tpl:settings-changed` desde el preview.
  - `README.md` del builder con estructura, sistema de temas, export y
    contrato de consumo hot.
  - Pint aplicado a todo el árbol (`vendor/bin/pint` verde).

### Añadido (2026-08-04)
- Creación del sistema de documentación `docs/`:
  - `docs/ARCHITECTURE.md` y subcarpetas `architecture/`, `design/`, `templates/`.
  - Documentos de arquitectura: backend, frontend, enrutado y especificaciones/tecnologías.
  - Documentos de diseño: sistema de tokens, catálogo de componentes y jerarquía de layouts.
  - Documentos de templates: sistema de layouts de UI (root, app shell, auth y nomenclatura).
- Creación de `CHANGELOG.md` (resueltos) y `TODO.md` (pendientes por prioridad).

## [Proyecto] — Scaffold inicial

### Versión base — `laravel/react-starter-kit`
- Backend: Laravel 13 (PHP 8.3), Fortify v1 (auth), Inertia v3, Wayfinder, Pest v4.
- Frontend: React 19, TypeScript 5, Tailwind CSS v4, shadcn/ui, Radix UI, Vite.
- Incluye: login, registro, reset de contraseña, verificación de email, 2FA, passkeys y ajustes (perfil/seguridad/apariencia) + dashboard de placeholders.
- Base de datos SQLite con migraciones de users, cache, jobs, passkeys y columnas 2FA.