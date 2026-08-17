# Jerarquía de Layouts

Los layouts se organizan en una jerarquía de **Wrappers/Templates**. La elección se realiza en `resources/js/app.tsx` en función del **nombre de la página Inertia**.

## Despachador de layouts (`app.tsx`)

```ts
layout: (name) => {
  switch (true) {
    case name === 'welcome':          return null;              // sin layout
    case name === 'templates/preview':return null;              // preview full-screen del tema
    case name.startsWith('auth/'):    return AuthLayout;        // AuthLayout
    case name.startsWith('settings/'):return [AppLayout, SettingsLayout]; // doble
    default:                          return AppLayout;         // AppLayout
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
```

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
│       ├── dashboard/ → dashboard-default-layout.tsx
│       ├── forms/     → forms-default-layout.tsx
│       └── auth/      → auth-default-layout.tsx, auth-default-login.tsx, auth-default-logout.tsx
```

Los temas de usuario son carpetas hermanas `layouts/{slug}/` con el mismo patrón. Estos paths se registran en `config/templates.php::defaults`. No participan en el catálogo público ni en la exportación.

## Layouts públicos

1. **`AppLayout`** (`layouts/app-layout.tsx`) — re-exporta el template de app; usado por páginas autenticadas (dashboard, settings y resto).
2. **`AuthLayout`** (`layouts/auth-layout.tsx`) — re-exporta el template auth simple; usado por páginas `auth/*`.
3. **`SettingsLayout`** (`layouts/settings/layout.tsx`) — sub-nav de ajustes; se monta **además** de `AppLayout` para páginas `settings/*` (layout compuesto): `[AppLayout, SettingsLayout]`.
4. **`templates/preview`** — **sin layout** (como `welcome`): el preview aplica el template del tema a toda la pantalla con un panel de controles flotante. Ver [`../templates/README.md`](../templates/README.md).

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