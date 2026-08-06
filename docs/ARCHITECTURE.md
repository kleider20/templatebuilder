# Arquitectura del Proyecto

> Documento de entrada principal. Para más detalle ver los subdirectorios:
>
> - [`README.md`](./README.md) — índice maestro + regla de mantenimiento + tabla de mapeo
> - [`architecture/`](./architecture/) — capas del sistema y especificaciones técnicas
> - [`design/`](./design/) — sistema de diseño y componentes
> - [`templates/`](./templates/) — sistema de layouts / templates de UI + **template builder**
> - [`../CHANGELOG.md`](../CHANGELOG.md) — cambios resueltos
> - [`../TODO.md`](../TODO.md) — cambios pendientes por prioridad

## Resumen ejecutivo

Aplicación web SPA construida sobre **Laravel 13** (PHP 8.3) con **Inertia v3 + React 19** y **Tailwind CSS v4**. El proyecto arranca del scaffold oficial **`laravel/react-starter-kit`**: incluye el sistema de autenticación completo (Fortify v1), un dashboard, páginas de ajustes y, desde el CONTRATO v7, un **template builder** de producto (catálogo, gestión, editor, preview multi-superficie, manifiestos y exportación con paridad de URLs).

## Stack tecnológico

| Capa | Tecnología | Versión |
|------|-----------|---------|
| Lenguaje backend | PHP | 8.3 |
| Framework | Laravel | 13.24.0 |
| Auth backend | Laravel Fortify | 1.37.3 |
| Frontend | React | 19.2 |
| Contrato SPA | Inertia | v3 (`inertia-laravel` 3.3.1 + `@inertiajs/react`) |
| Estilos | Tailwind CSS | v4 |
| Tipado | TypeScript | 5.7 |
| Editor de código | `@monaco-editor/react` | 4.7 |
| Primitivas UI | Radix UI | react-* (varios) |
| Build | Vite | 8 |
| Router tipado | Laravel Wayfinder | 0.1.21 |
| Base de datos | SQLite | — |
| Tests | Pest | 4.7.8 |

> Tabla completa y detallada con versiones exactas: [`architecture/specifications.md`](./architecture/specifications.md).

## Flujo de datos

```
Navegador (React SPA)
   │  HTTP (Inertia)
   ▼
Laravel (rutas web/) ──► Controller ──► (Acciones/Requests)
   │                                      │  Eloquent
   ▼                                      ▼
Inertia::render('page', props)        Base de datos (SQLite)
   │
   ▼
resources/js/pages/*.tsx (componenet React por ruta)
   ▼
Layout(Wrappers) + hooks + componentes UI/shadcn
```

- El servidor renderiza la primera visita vía **SSR opcional**; la navegación posterior es cliente mediante `@inertiajs/vite` (XRH). No se usa Axios.
- La ruta del frontend se resuelve por el **nombre de la página Inertia** (`name`) en `resources/js/app.tsx`, no por una rutera React manual.
- Las llamadas al backend desde el cliente se hacen con funciones tipadas generadas por **Wayfinder** (`@/actions/*`, `@/routes/*`).

## Estructura raíz relevante

```
templatebuilder/
├── app/                    (backend Laravel)
│   ├── Http/Controllers/   (incl. Template*, Settings/)
│   ├── Models/             (User, TemplateVersion)
│   └── Services/Templates/ (manifiesto, scaffold, export, DTOs)
├── bin/                    (scripts Node: build-template.mjs, build-vendor-react.mjs)
├── bootstrap/              (providers + middleware app)
├── config/                 (configuraciones; incl. templates.php)
├── database/               (migraciones, factories, seeders)
├── docs/                   (documentación — este directorio)
├── output/                 (ZIPs de export del template builder)
├── public/vendor/react/    (bundle ESM de react generado por build:vendor)
├── resources/
│   ├── css/app.css         (Tailwind v4 + tokens)
│   ├── js/                 (frontend React/Inertia)
│   └── views/app.blade.php (raíz Blade)
├── routes/                 (web, settings, templates, console)
├── tests/                  (Pest; incl. templates/ y auth/)
├── CHANGELOG.md
└── TODO.md
```

## Índices de profundidad

| Tema | Documento |
|------|-----------|
| Búsqueda backend (models, controllers, actions, requests, DB) | [`architecture/backend.md`](./architecture/backend.md) |
| Búsqueda frontend (páginas, layouts, hooks, types, convención) | [`architecture/frontend.md`](./architecture/frontend.md) |
| Enrutado completo (web, settings, **templates**, fortify, console) | [`architecture/routing.md`](./architecture/routing.md) |
| Especificaciones / tecnologías con versiones | [`architecture/specifications.md`](./architecture/specifications.md) |
| Tokens, tema, dark/light mode | [`design/system.md`](./design/system.md) |
| Catálogo de componentes | [`design/components.md`](./design/components.md) |
| Jerarquía de layouts y variantes | [`design/layouts.md`](./design/layouts.md) |
| Sistema de templates de UI + **template builder** | [`templates/README.md`](./templates/README.md) |

## Convenciones globales

- **Archivos en `resources/js/`:** nombres en **kebab-case** (páginas, componentes, layouts, hooks).
- **Controllers delgados:** validación en `FormRequest` (`app/Http/Requests/...`), lógica de negocio en Services/Actions fuera del controller.
- **Nombres descriptivos** en métodos y variables (ej. `isRegisteredForDiscounts`).
- **TypeScript estricto:** todo el frontend es `.ts`/`.tsx`, no JavaScript plano.
- **Tests obligatorios** en Pest para cada cambio de comportamiento.
- **Flujo documentación:** antes de cualquier cambio leer solo el/los `docs/` que correspondan (ver [`README.md`](./README.md)), pedir confirmación, aplicar y actualizar el doc en el mismo turno (regla grabada en `AGENTS.md`).