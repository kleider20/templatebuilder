# Especificaciones y Tecnologías

Documento con las especificaciones técnicas exactas del proyecto (versiones reales instaladas, no rangos).

## Especificaciones de la aplicación

| Especificación | Valor |
|----------------|-------|
| Nombre del paquete | `laravel/react-starter-kit` |
| Tipo | Project (web app) |
| Licencia | MIT |
| Entorno de ejecución | PHP 8.3 + Node.js |
| Base de datos activa | SQLite (`DB_CONNECTION=sqlite`) |
| Motor de plantillas servidor | Blade (solo raíz `app.blade.php`) |
| Rendering SPA | Inertia v3 (cliente + SSR opcional) |
| Idioma | Ajustable vía `APP_LOCALE` (por defecto `en`) |

## Backend — Paquetes PHP (Composer)

### Producción (`require`)

| Paquete | Versión | Rol |
|---------|---------|-----|
| `php` | ^8.3 | Lenguaje |
| `laravel/framework` | 13.24.0 | Framework |
| `inertiajs/inertia-laravel` | 3.3.1 | Adapter backend de Inertia |
| `laravel/fortify` | 1.37.3 | Backend de autenticación |
| `laravel/wayfinder` | 0.1.21 | Generación de funciones TS de rutas/actions |
| `laravel/tinker` | 3.0.2 | REPL de Artisan |
| `laravel/chisel` | 0.1.1 | Tooling de scripts |

### Desarrollo (`require-dev`)

| Paquete | Versión | Rol |
|---------|---------|-----|
| `pestphp/pest` | 4.7.8 | Framework de testing |
| `pestphp/pest-plugin-laravel` | 4.1.0 | Integración Pest–Laravel |
| `laravel/pint` | 1.30.3 | Formateador de código |
| `larastan/larastan` | 3.10.0 | PHPStan para Laravel |
| `laravel/boost` | 2.5.0 | Aceleración de desarrollo asistida por IA |
| `laravel/pail` | 1.2.7 | Logs en terminal |
| `laravel/pao` | 1.1.3 | Output optimizado de tests |
| `laravel/sail` | 1.65.0 | Docker para Laravel |
| `mockery/mockery` | 1.6.12 | Mocking |
| `nunomaduro/collision` | 8.9.5 | Manejo de errores CLI |
| `fakerphp/faker` | 1.24.1 | Datos falsos |

## Frontend — Paquetes npm (Node)

> Versiones mostradas según `package.json` (rangos `^`). El editor del builder usa **Monaco**.

### Dependencias (`dependencies`)

| Paquete | Versión | Rol |
|---------|---------|-----|
| `react` / `react-dom` | ^19.2.0 | UI / rendering DOM |
| `@inertiajs/react` / `@inertiajs/vite` | ^3.0.0 | Integración Inertia–React + plugin Vite |
| `@laravel/passkeys` | ^0.2.0 | Cliente passkeys / WebAuthn |
| `@monaco-editor/react` | ^4.7.0 | Editor de código del template builder |
| `tailwindcss` | ^4.0.0 | Framework de estilos |
| `@tailwindcss/vite` | ^4.1.11 | Plugin Vite de Tailwind |
| `typescript` | ^5.7.2 | Tipado |
| `vite` | ^8.0.0 | Bundler |
| `laravel-vite-plugin` | ^3.0.0 | Plugin Vite de Laravel |
| `@vitejs/plugin-react` | ^5.2.0 | Soporte React en Vite |
| `@laravel/vite-plugin-wayfinder` | ^0.1.3 | Generación de Wayfinder en build |
| `@radix-ui/react-*` | varios | Primitivas UI (avatar, checkbox, collapsible, dialog, dropdown-menu, label, navigation-menu, select, separator, slot, toggle, toggle-group, tooltip) |
| `class-variance-authority` | ^0.7.1 | Variantes de clases |
| `clsx` | ^2.1.1 | Combinación de clases |
| `tailwind-merge` | ^3.0.1 | Merge de clases Tailwind |
| `lucide-react` | ^0.475.0 | Iconos |
| `sonner` | ^2.0.0 | Toasts/notificaciones |
| `input-otp` | ^1.4.2 | Campo OTP (2FA) |
| `tw-animate-css` | ^1.4.0 | Animaciones CSS |
| `concurrently` | ^9.0.1 | Procesos en paralelo (dev) |
| `globals` | ^15.14.0 | Globals de lint |
| `babel-plugin-react-compiler` | ^1.0.0 | Compilador React |

### DevDependencies (`devDependencies`)

| Paquete | Versión | Rol |
|---------|---------|-----|
| `@types/react` | ^19.2.0 | Tipos de React |
| `@types/react-dom` | ^19.2.0 | Tipos de React DOM |
| `@types/node` | ^22.13.5 | Tipos de Node |
| `eslint` | ^9.17.0 | Linter |
| `@eslint/js` | ^9.19.0 | Config base de ESLint |
| `typescript-eslint` | ^8.23.0 | ESLint + TS |
| `@stylistic/eslint-plugin` | ^5.10.0 | Reglas de estilo |
| `eslint-plugin-react` | ^7.37.3 | Reglas React |
| `eslint-plugin-react-hooks` | ^7.0.0 | Reglas de hooks |
| `eslint-plugin-import` | ^2.32.0 | Reglas de imports |
| `eslint-config-prettier` | ^10.0.1 | Compatibilidad Prettier |
| `eslint-import-resolver-typescript` | ^4.4.4 | Resolver TS en imports |
| `prettier` | ^3.4.2 | Formateador |
| `prettier-plugin-tailwindcss` | ^0.6.11 | Orden de clases Tailwind |
| `esbuild` | ^0.28.1 | Bundler auxiliar (build de vendor) |
| `@tailwindcss/cli` | ^4.3.3 | CLI Tailwind |

## Herramientas y scripts

| Script | Comando | Descripción |
|--------|---------|-------------|
| Build | `npm run build` | Build de Vite |
| Build vendor React | `npm run build:vendor` | Genera `public/vendor/react/index.js` (bundle ESM de react para el preview vía import map) |
| Build template | `node bin/build-template.mjs {slug}` | Compila un tema del builder a `dist/` |
| Dev | `npm run dev` / `composer run dev` | Vite + servidor + queue |
| Lint | `npm run lint` / `composer run lint` | ESLint / Pint |
| Typecheck | `npm run types:check` / `composer run types:check` | `tsc --noEmit` / PHPStan |
| Formato | `npm run format` | Prettier |
| Tests | `php artisan test` | Pest |
| CI | `composer run ci:check` | lint + formato + tipos + tests |

## Configuración clave

- **`components.json`** — config de shadcn/ui (alias `@/`).
- **`tsconfig.json`** — strict, paths `@/*` → `resources/js/*`, `~/` → `vendor`.
- **`vite.config.ts`** — plugins: `laravel`, `inertia`, `react` (con `babel-plugin-react-compiler`), `tailwindcss`, `wayfinder` (`formVariants: true`); fuente `Instrument Sans` vía Bunny.
- **`config/templates.php`** — dominio del builder: `base_path` (`resources/js/layouts`), `surfaces` (landing/dashboard/forms/auth), `defaults`, `theme_roles`, `min_palettes`, `fallback_palette`, `url_prefix`, `output_path`, `editor_extensions`.
- **`.env`** — SQLite, sesiones en DB, cola en DB, cache en DB, mailer `log`.