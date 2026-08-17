# Frontend (React / Inertia)

Capa cliente construida con **React 19 + Inertia v3 + TypeScript + Tailwind CSS v4**. Todo el código fuente está en TypeScript (`.ts`/`.tsx`), sin JavaScript plano.

## Estructura de `resources/js/`

```
resources/
├── css/app.css
├── js/
│   ├── app.tsx                 # Entrada de Inertia (createInertiaApp)
│   ├── actions/                # Controller-actions generados por Wayfinder (PascalCase)
│   ├── components/             # Componentes React
│   │   ├── ui/                 # Primitivas shadcn/Radix (26)
│   │   ├── manifest-editor.tsx # Editor del manifiesto (modal JSON)
│   │   ├── template-preview-surface.tsx  # Superficie de render de preview (multi-superficie) — renderiza la fuente del tema vía import.meta.glob
│   │   └── *.tsx               # Componentes de dominio/app (29 en total)
│   ├── hooks/                  # Hooks (use-appearance, use-flash-toast, use-template-settings…)
│   ├── layouts/                # Layouts (Wrappers/Templates + superficies default/)
│   ├── lib/utils.ts            # cn(), toUrl()
│   ├── pages/                  # Páginas por ruta Inertia (19)
│   ├── routes/                 # Named-routes generados por Wayfinder
│   ├── types/                  # Tipos TS (auth, navigation, ui, global)
│   └── wayfinder/              # Runtime helpers de Wayfinder
└── views/app.blade.php         # Raíz Blade única
```

## Entrada de la aplicación (`app.tsx`)

- `createInertiaApp` con template de título `"{title} - {appName}"`.
- **Despachador de layout** por nombre de página:
  - `welcome` → Layout `null` (sin layout).
  - `auth/*` → `AuthLayout`.
  - `settings/*` → `[AppLayout, SettingsLayout]`.
  - resto → `AppLayout`.
- `strictMode: true`; `withApp` envuelve en `TooltipProvider` + `<Toaster>`.
- `progress.color: '#4B5563'`.
- Llama `initializeTheme()` al cargar (gestión de tema claro/oscuro).

## Páginas (`pages/`)

19 páginas bajo los espacios `auth/`, `settings/`, `templates/` y raíz:

- **Raíz:** `welcome.tsx` (landing, sin layout), `dashboard.tsx` (placeholders + breadcrumbs).
- **auth/** (`login`, `register`, `forgot-password`, `reset-password`, `confirm-password`, `verify-email`, `two-factor-challenge`) — formularios Fortify sobre `AuthLayout`.
- **settings/** (`profile`, `security`, `appearance`) — sobre `[AppLayout, SettingsLayout]`.
- **templates/** (`index`, `create`, `editor`, `preview`, `versions`) — template builder sobre `AppLayout` (caen en el `default` del despachador). El catálogo (`index`) incluye por tarjeta los botones **Editor / Preview / Exportar / Subir preview** (este último sube la imagen vía `templates.thumbnail.update` con `FormData` y recarga la lista).

Cada página expone `.layout = { title, description }` (auth) o `.layout = { breadcrumbs }` (dashboard/settings/templates).

## Hooks

- `hooks/use-appearance.tsx` — sistema de tema: `initializeTheme()`, `useAppearance()` (vía `useSyncExternalStore`), persiste en `localStorage` + cookie SSR `appearance`.
- `hooks/use-flash-toast.ts` — consumo de mensajes flash.
- `hooks/use-template-settings.ts` — `useTemplateSettings()` / `useTemplateSetting()` para leer ajustes del tema en el preview.
- `hooks/use-clipboard.ts` — utilidad de portapapeles.
- `hooks/use-current-url.ts` — URL actual (resaltar sub-nav).
- `hooks/use-initials.tsx` — iniciales del usuario (avatar).
- `hooks/use-mobile.tsx` — detección de móvil.
- `hooks/use-mobile-navigation.ts` — navegación móvil.
- `hooks/use-two-factor-auth.ts` — gestión de 2FA.

## Tipos (`types/`)

- `index.ts` — re-exporta auth/navigation/ui.
- `auth.ts` — `User`, `Auth`, `Passkey`, `TwoFactorSetupData`, `TwoFactorSecretKey`.
- `navigation.ts` — `BreadcrumbItem`, `NavItem` (usa `InertiaLinkProps['href']`, `LucideIcon`).
- `ui.ts` — `AppLayoutProps`, `AppVariant`, `FlashToast`, `AuthLayoutProps`.
- `global.d.ts` — augmentación de módulos: `passwordrules`, props compartidas `name`/`auth`/`sidebarOpen`.
- `vite-env.d.ts` — referencia a `vite/client`.

## Wayfinder (llamadas tipadas)

- `actions/` — funciones por controller PHP (PascalCase), métodos `.url()`, `.get()`, `.post()`, `.form()`. Incluye `templates` (`index`, `create`, `store`, `editor`, `preview`, `export`…).
- `routes/` — named-routes en kebab-case (módulos: login, logout, register, home, dashboard, security, profile, two-factor, settings, templates, etc.). El módulo `templates/preview` expone `show` para el GET de preview.
- `wayfinder/index.ts` — helpers `queryParams`, `setUrlDefaults`, `addUrlDefault`, etc.
- Configurado en `vite.config.ts` con `formVariants: true`.
- **Nota**: el helper de export del tema se llama `exportMethod` (no `export`, palabra reservada) — ver `.ai/rules/templates.md`.

## Nomenclatura

- **Todos** los archivos de `resources/js/` usan **kebab-case** (páginas, componentes, layouts, hooks, rutas generadas).
- Excepción: `actions/` usa **PascalCase** porque refleja clases PHP; `routes/` usa kebab-case con subdirectorios.
- Los componentes `ui/` usan **exportaciones nombradas** (`export function Button`); los top-level usan en su mayoría **default exports**, salvo wrappers multi-componente (`AppContent`, `AppShell`, `AppSidebar`).

## Detalle por tema

- Sistema de diseño y tokens → [`../design/system.md`](../design/system.md).
- Catálogo de componentes → [`../design/components.md`](../design/components.md).
- Jerarquía de layouts → [`../design/layouts.md`](../design/layouts.md) y [`../templates/README.md`](../templates/README.md).