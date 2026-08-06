# CHANGELOG

Registro de cambios **resueltos** en el proyecto, ordenados de más reciente a más antigua y agrupados por versión/fecha.

> **Regla de uso:** cada tarea que el asistente resuelva se agrega aquí con su fecha. Los pendientes van en [`TODO.md`](./TODO.md), **no** aquí.

Formato basado en [Keep a Changelog](https://keepachangelog.com/es-ES/1.0.0/) + [SemVer](https://semver.org/).

## [Sin publicar]

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