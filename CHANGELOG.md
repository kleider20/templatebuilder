# CHANGELOG

Registro de cambios **resueltos** en el proyecto, ordenados de más reciente a más antigua y agrupados por versión/fecha.

> **Regla de uso:** cada tarea que el asistente resuelva se agrega aquí con su fecha. Los pendientes van en [`TODO.md`](./TODO.md), **no** aquí.

Formato basado en [Keep a Changelog](https://keepachangelog.com/es-ES/1.0.0/) + [SemVer](https://semver.org/).

## [Sin publicar]

### Añadido: Tabs por tipo en el catálogo `dashboard/plantillas` (2026-08-20)
- **Tabs por tipo** en el catálogo (`plantillas.tsx`): control segmentado (patrón `appearance-tabs.tsx`) construido desde la prop `surfaces` (`Todos | Landing | Dashboard | Auth | Forms`). Un tema multi-tipo aparece en cada tab que le corresponda; "Todos" muestra todos.
- **Filtro de precios conservado** (Todos/Gratis/Premium): ambos controles se combinan (`matchesType && matchPricing`).
- Estado vacío cuando no hay plantillas para el filtro activo ("No hay plantillas para este filtro.").
- Sin cambios de backend: reutiliza `surfaces` y `types` del payload de `DashboardPlantillasController`. Suite `DashboardPlantillasTest` en verde, `types:check` y `eslint` en verde.
- Docs actualizados: `architecture/frontend.md` y `templates/README.md` (mapa de decisiones).

### Eliminado: catálogo `/templates` (templates.index) — el catálogo vive solo en `/dashboard/plantillas` (2026-08-18)
- **Se eliminó la página `templates/index`**: ruta `templates.index` (`GET /templates`) fuera de `routes/templates.php` (ahora `GET /templates` responde 405; el POST `/` de `store` se conserva), página `resources/js/pages/templates/index.tsx` eliminada y método `TemplateController@index` + su dependencia `CatalogTemplatesAction` retirados.
- **Catálogo canónico en `dashboard.plantillas`**: `DashboardPlantillasController` envía el payload completo del catálogo (`templates`, `surfaces`, `canCreate`, `canViewAll`) y `dashboard/plantillas/plantillas.tsx` pasa a ser el catálogo del builder (filtros Todos/Gratis/Premium, tarjetas con Editor/Preview/Exportar/Subir preview, botón Crear plantilla, breadcrumbs Dashboard → Plantillas). Se muestra dentro del chrome del dashboard (`DashboardLayout`).
- **Sidebar del dashboard**: se eliminó el ítem "Editor" (apuntaba a `/templates`; el editor por plantilla se alcanza desde las tarjetas del catálogo).
- **Fallbacks del editor**: `TemplateEditorController` ahora redirige a `dashboard.plantillas` (antes `templates.index`).
- Wayfinder regenerado (módulo `routes/templates` sin `index`). Se conservan intactos `editor`, `preview`, `marketplace` (público, `/marketplace`), `versions`, `export` y las URLs públicas de paridad de artefactos.
- Tests: `DashboardPlantillasTest` actualizado (payload completo + `GET /templates` → 405). Suite completa verde (89 ok + 1 skip), Pint y `types:check` en verde.
- Docs actualizados: `architecture/frontend.md`, `templates/README.md` (mapa de decisiones), `design/layouts.md` y reglas en `.ai/rules/pages.md` y `.ai/rules/layouts.md`.

### Añadido: Dashboard del tema default conectado a rutas reales + catálogo `dashboard.plantillas` (2026-08-18)
- **Template dashboard del tema default aplicado al sistema**: nuevo wrapper de superficie `layouts/surfaces/dashboard-layout.tsx`; el dispatcher de `app.tsx` asigna `dashboard/*` → `DashboardLayout` (antes `AppLayout`). `/dashboard` y `/dashboard/plantillas` ahora usan el dashboard del tema default.
- **Chrome completo con rutas reales** en `layouts/default/components/dashboard/`: `dashboard-default-sidebar.tsx` (Dashboard, Plantillas, Editor, Passkeys, Configuración; activo por URL; dropdown de avatar con Mi Perfil/Passkeys/Cerrar sesión vía `logout`) y `dashboard-default-topbar.tsx` (hamburguesa, búsqueda Ctrl+K que salta a Plantillas, toggle de tema con `useAppearance`, campana, ayuda, badge Production). `dashboard-default-layout.tsx` compone sidebar + topbar + `<main>`.
- **Nueva ruta `dashboard.plantillas`** (GET `dashboard/plantillas`, grupo `auth`+`verified`): `DashboardPlantillasController` autoriza `viewAny` Template y renderiza el catálogo con `templates` + `canCreate` desde `CatalogTemplatesAction`.
- **Página `dashboard/plantillas/plantillas.tsx`**: catálogo real con filtros por superficie, vista grid/lista, tarjetas con pricing, acciones Vista previa / Editar / Exportar, y paginación. Tokens usados son los definidos en `app.css` (no `bg-surface`/`text-content`, exclusivos de CSS de tema).
- Wayfinder regenerado (`plantillas` en `resources/js/routes/dashboard/index.ts`). Lint pre-existente de `landing-default-layout.tsx` corregido.
- Tests: `tests/Feature/Dashboard/DashboardPlantillasTest.php` (redirect sin auth, catálogo admin, 403 sin permiso). Suite completa verde (88 ok + 1 skip), Pint y `types:check` en verde.
- Docs actualizados: `architecture/frontend.md`, `design/layouts.md`, `templates/README.md` (mapa de decisiones) y regla en `.ai/rules/layouts.md`.

### Añadido: Reorganización de páginas por superficie + wrappers de tema (2026-08-18)
- **Páginas por superficie**: `pages/{superficie}/{pagina}/{pagina}.tsx` — `welcome.tsx` → `landing/welcome/welcome.tsx`, `dashboard.tsx` → `dashboard/dashboard/dashboard.tsx`, y las 7 páginas auth → `auth/{pagina}/{pagina}.tsx`. El nombre Inertia es el path completo (p.ej. `landing/welcome/welcome`). Actualizados `routes/web.php`, `FortifyServiceProvider` y assertions de tests (`PasswordConfirmationTest`, `TwoFactorChallengeTest`).
- **Wrappers de superficie**: `layouts/surfaces/landing-layout.tsx` (envuelve `landing-default-template`) y `layouts/surfaces/forms-layout.tsx` (envuelve `forms-default-template`). Dispatcher de `app.tsx` por prefijo: `landing/*` → `LandingLayout`, `forms/*` → `FormsLayout`, `templates/preview`/`marketplace` → `null`, `auth/*` → `AuthLayout`, `settings/*` → doble, resto → `AppLayout`.
- **`welcome.tsx` ahora es solo contenido**: el chrome (header/nav/footer) lo provee el tema; se añadió nav auth-aware (Dashboard / Log in / Register) a `landing-default-layout.tsx`.
- Regla registrada en `.ai/rules/pages.md` (glob `resources/js/pages/**`): páginas por superficie/tipo, sin layout propio (lo asigna el dispatcher).
- Docs actualizados: `architecture/frontend.md`, `design/layouts.md`, `templates/README.md` (mapa de decisiones), `templates/nomenclature.md`. TODO B5 refinado (falta persistencia + inyección dinámica del tema activo).
- Tests: suite completa verde (85 ok + 1 skip; el manifest de Vite se regeneró con `npm run build`), Pint y `types:check` en verde.

### Añadido: Comercialización B1–B3 — pricing, marketplace público y distribución/instalación (2026-08-18)
- **B1 Pricing**: bloque `pricing` en `template.json` (`{type: free|premium, price, currency: 'USD'}`). `TemplateManifestData` lo normaliza (`free` → `price: null`; `currency` en mayúsculas, default `USD`), `TemplateManifestService::validatePricing` (premium exige `price > 0`), `TemplateScaffoldService` genera temas default gratis. `UpdateManifestAction`/`UpdateTemplateManifestRequest`/`TemplateEditorController@updateManifest` lo propagan. UI: `types/templates.ts` (`TemplatePricing`), catálogo `index.tsx` con badge FREE (verde) o precio US$ y filtro Todos/Gratis/Premium, editor `manifest-editor.tsx` con selector Gratis/Premium + precio.
- **B2 Marketplace**: `TemplateMarketplaceController` + `MarketplaceTemplatesAction` (listan temas default + registrados con pricing). Rutas públicas `GET /templates/marketplace` (`marketplace`) y `GET /templates/marketplace/{slug}/download` (`marketplace.download`, 404 sin `TemplateVersion`). Página `resources/js/pages/templates/marketplace.tsx` sin chrome del builder (`app.tsx` → `null`).
- **B3 Distribución/instalación**: el ZIP de export incluye `install.php` + `README.md` renderizados desde `resources/stubs/commercial/`. Nuevo `TemplateImportService` (`importFromZip`/`importFromSlug`/`install`/`ensureBuilt`) y comando `php artisan template:import {source} {--force} {--user=}`; registra la propiedad en `templates` solo con `--user` (columna NOT NULL) y recompila `dist/` si falta.
- Regla de organización de componentes registrada: `resources/js/components/default/{slug}/` (default, internos) vs `resources/js/components/{slug}/` (comerciales/nuevos); `.gitkeep` para carpetas vacías.
- Tests: pricing en `ManifestTest`/`EndpointsTest`, ZIP con instalador en `ExportTest`, `ImportTest` nuevo (5 tests). 35 ok + 1 skip, Pint y `tsc` en verde.

### Fix "react/compiler-runtime" externalizado — crash de la app en dev y producción (2026-08-17)
- **Causa raíz**: `babel-plugin-react-compiler` genera `import { c } from 'react/compiler-runtime'` en los componentes, pero `react/compiler-runtime` **no** estaba en `build.rollupOptions.external` de `vite.config.ts` ni en el import map de `app.blade.php`. El build de producción empaquetaba `react/compiler-runtime` (CJS) en un chunk propio que hacía `require('react')` en el navegador → `Uncaught Error: Calling require for "react"...`; en dev, la resolución del specifier fallaba (`Failed to resolve module specifier "react"`) → la app se quedaba cargando y los temas no montaban. Esto era la fuente de fondo del preview "se queda cargando" y de los recargos repetidos de páginas.
- **`vite.config.ts`**: `external` ahora incluye `react/compiler-runtime` (además de `react`, `react/jsx-runtime`, `react/jsx-dev-runtime`, `react-dom`, `react-dom/client`).
- **Vendor React**: `.build/vendor-react/full-entry.tsx` importa `react/compiler-runtime` y re-exporta su `c` (useMemoCache), así el import map resuelve `react/compiler-runtime` a la MISMA instancia única de React compartida con los temas. Regenerado con `node bin/build-vendor-react.mjs` → `public/vendor/react/index.js`.
- **`app.blade.php`**: el import map ahora mapea también `react/compiler-runtime` → `/vendor/react/index.js?v=<md5>`.
- **Build de producción regenerado** (`npm run build`): ya no existe el chunk CJS `compiler-runtime-*.js`, no hay `require('react')`, y los chunks importan `{ c } from 'react/compiler-runtime'` como specifier externo resuelto por el import map.

### Fix "la página del preview se queda cargando" — fallback de build robusto + caché (2026-08-17)
- **Causa raíz**: al entrar al preview con el dev server de Vite caído (suspensión de la máquina o cierre manual), Laravel servía los assets de **producción** (`/build/assets/...`) y el chunk `dist-*.js` importaba `StrictMode` desde `'react'` (externalizado), resuelto por el import map a `/vendor/react/index.js`. Con el vendor viejo cacheado por el navegador (el import map no tenía cache-busting), la app moría con `SyntaxError: 'react' does not provide an export named 'StrictMode'` y el preview quedaba en blanco/spinner infinito. Con Vite caído pero `public/hot` presente, además fallaba el import dinámico de `pages/templates/preview.tsx` (barra de progreso de Inertia atascada).
- **Cache-busting del import map**: en `resources/views/app.blade.php` las 5 URLs del vendor (`/vendor/react/index.js`) ahora llevan `?v={{ md5_file(public_path('vendor/react/index.js')) }}`, de modo que al regenerar el bundle vendor el navegador jamás reutiliza el archivo viejo cacheado.
- **Header no-store en el preview**: nuevo middleware `app/Http/Middleware/NoStore.php` aplicado a `GET /templates/{slug}/preview` → `Cache-Control: no-store` (convierte la `Inertia\Response` con `toResponse()`), evitando que el navegador sirva HTML viejo del preview que apunte a assets de build cuando Vite ya volvió.
- **Build de producción regenerado** (`npm run build`): el modo fallback queda al día con el código actual y funciona con el vendor corregido.
- Tests nuevos en `EndpointsTest`: `preview show` responde sin caché y el import map lleva `?v=`. Suite 72 ok + 1 skip, Pint y `tsc` en verde.

### Fix bucle de recarga en Preview y Named Exports de React en Vendor (2026-08-17)
- **Corrección del bucle de recarga en el Preview**:
  - `preview.tsx`: se asignó explícitamente `type="button"` a los botones de la barra de controles flotantes (ocultar controles, modo oscuro, restablecer) para prevenir cualquier comportamiento de submit/recarga accidental.
  - `template-preview-surface.tsx`: se estabilizó la emisión del evento `tpl:settings-changed` serializando con `JSON.stringify(settings)` en el `useEffect` para evitar renders en bucle por cambio de referencia de objetos.
- **Exportación de named exports en Bundle Vendor React**:
  - `bin/vendor-react/full-entry.tsx` ahora exporta explícitamente `StrictMode`, `Suspense`, `Profiler`, `useActionState`, y `useOptimistic` desde la instancia de React, solucionando el `SyntaxError: The requested module 'react' does not provide an export named 'StrictMode'` al consumir build assets en producción.
  - Regenerado `/vendor/react/index.js` con `npm run build:vendor` (`node bin/build-vendor-react.mjs`).
- Artefactos de aurora recompilados (`node bin/build-template.mjs aurora`), Pint, tipos TS y tests (70 pasando) verificados en verde.

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