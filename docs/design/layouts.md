# Jerarquía de Layouts

Los layouts se organizan en una jerarquía de **Wrappers/Templates**. La elección se realiza en `resources/js/app.tsx` en función del **nombre de la página Inertia** (path completo relativo a `pages/`, p.ej. `landing/welcome/welcome`).

## Despachador de layouts (`app.tsx`)

```ts
layout: (name) => {
  switch (true) {
    case name.startsWith('landing/'): return LandingLayout;       // surface: template landing activo
    case name.startsWith('forms/'):   return FormsLayout;         // surface: template forms activo
    case name === 'templates/preview':return null;                // preview full-screen del tema
    case name === 'templates/marketplace': return null;           // marketplace público sin chrome
    case name.startsWith('auth/'):    return AuthLayout;          // AuthLayout
    case name.startsWith('settings/'):return [AppLayout, SettingsLayout]; // doble
    case name.startsWith('dashboard/'): return DashboardLayout;   // surface: template dashboard del tema default
    default:                          return AppLayout;           // AppLayout (templates/* sin index, resto)
  }
}
```

## Estructura

```
app-layout.tsx (re-exporta AppLayoutTemplate)
├── app/app-sidebar-layout.tsx   ← template activo (AppShell variant="sidebar")
│   ├── components/app-shell.tsx
│   ├── components/app-sidebar.tsx
│   ├── components/app-content.tsx
│   └── components/app-sidebar-header.tsx
└── app/app-header-layout.tsx    ← alternativa definida (variant="header", NO usada)

auth-layout.tsx (re-exporta auth-simple-layout)
├── auth/auth-simple-layout.tsx  ← template activo (card centrada)
├── auth/auth-card-layout.tsx    ← definido (card), NO usado
└── auth/auth-split-layout.tsx   ← definido (split-screen), NO usado

settings/layout.tsx              ← sub-navegación (Profile/Security/Appearance)

surfaces/
├── landing-layout.tsx           ← aplica el template landing activo (hoy tema default)
├── forms-layout.tsx             ← aplica el template forms activo (hoy tema default)
└── dashboard-layout.tsx         ← aplica el template dashboard del tema default
```

## Superficies (wrappers por familia de páginas)

Los **wrappers de superficie** (`layouts/surfaces/`) aplican el template de una superficie a **todas** las páginas de su carpeta. Viven en `pages/{superficie}/` y el dispatcher los asigna por prefijo:

- `LandingLayout` (`surfaces/landing-layout.tsx`) → envuelve `landing-default-template` (template **landing** activo). Aplica el chrome del tema (header/footer/nav) a páginas públicas como `landing/welcome/welcome`.
- `FormsLayout` (`surfaces/forms-layout.tsx`) → envuelve `forms-default-template` (template **forms** activo) para páginas `forms/*`.
- `DashboardLayout` (`surfaces/dashboard-layout.tsx`) → envuelve `dashboard-default-template` (template **dashboard** del tema default) para páginas `dashboard/*`. El template incluye **sidebar y topbar con rutas reales del proyecto** (ver `layouts/default/components/dashboard/`).

Por ahora los tres usan el tema `default`; B5 inyectará el **tema activo persistido** de forma dinámica (cambiar el tema en el builder cambia automáticamente todas las páginas de esa superficie). Las páginas no definen layout: lo asigna el dispatcher por superficie (`.ai/rules/pages.md`).

## Superficies del template builder (`layouts/default/`)

Además de los layouts del sistema, existe la carpeta de **temas default** usada por el template builder (fallback y base de scaffolds, NO exportables). Sigue el mismo naming que los temas: templates en la raíz y layouts/roles en `default/components/{superficie}/`:

```
layouts/
├── default/
│   ├── landing-default-template.tsx ← config: default 'landing'
│   ├── dashboard-default-template.tsx
│   ├── forms-default-template.tsx
│   ├── auth-default-template.tsx
│   └── components/
│       ├── landing/   → landing-default-layout.tsx
│       ├── dashboard/ → dashboard-default-layout.tsx, dashboard-default-sidebar.tsx, dashboard-default-topbar.tsx
│       ├── forms/     → forms-default-layout.tsx
│       └── auth/      → auth-default-layout.tsx, auth-default-login.tsx, auth-default-logout.tsx
```

El template dashboard del tema default provee el **chrome completo** de las páginas `dashboard/*` (sidebar de navegación con rutas reales del proyecto, topbar con tema/búsqueda, avatar y logout). Cualquier cambio visual del dashboard de la app se edita en `layouts/default/components/dashboard/*` vía `DashboardLayout` (ver `.ai/rules/layouts.md`).

Los temas de usuario son carpetas hermanas `layouts/{slug}/` con el mismo patrón. Estos paths se registran en `config/templates.php::defaults`. No participan en el catálogo público ni en la exportación.

## Layouts públicos

1. **`AppLayout`** (`layouts/app-layout.tsx`) — re-exporta el template de app; usado por páginas autenticadas (settings y resto).
2. **`AuthLayout`** (`layouts/auth-layout.tsx`) — re-exporta el template auth simple; usado por páginas `auth/*`.
3. **`SettingsLayout`** (`layouts/settings/layout.tsx`) — sub-nav de ajustes; se monta **además** de `AppLayout` para páginas `settings/*` (layout compuesto): `[AppLayout, SettingsLayout]`.
4. **`templates/preview`** — **sin layout**: el preview aplica el template del tema a toda la pantalla con un panel de controles flotante. Ver [`../templates/README.md`](../templates/README.md).
5. **`templates/marketplace`** — **sin layout**: el marketplace es una página pública (sin login) y se renderiza sin el chrome del builder (`app.tsx` devuelve `null`).
6. **`LandingLayout`** / **`FormsLayout`** / **`DashboardLayout`** (`layouts/surfaces/`) — wrappers de superficie que aplican el template landing/forms/dashboard activo (tema `default`) a `landing/*`, `forms/*` y `dashboard/*`.

## Layouts compuestos

Las páginas `settings/*` usan **dos layouts en cadena** (Inertia los apila de fuera a dentro):

```
AppLayout (shell sidebar)
  └── SettingsLayout (sub-nav interno)
        └── Página (profile / security / appearance)
```

Los `breadcrumbs` los aporta cada página vía `page.layout = { breadcrumbs }`.

## Detalle por family

- Estructura y componentes del shell → [`../templates/app-templates.md`](../templates/app-templates.md).
- Variantes de auth → [`../templates/auth-templates.md`](../templates/auth-templates.md).
- Wrappers → [`../templates/root-layouts.md`](../templates/root-layouts.md).
- Convención Wrapper/Template → [`../templates/nomenclature.md`](../templates/nomenclature.md).