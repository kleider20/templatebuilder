# Jerarquía de Layouts

Los layouts se organizan en una jerarquía de **Wrappers/Templates**. La elección se realiza en `resources/js/app.tsx` en función del **nombre de la página Inertia**.

## Despachador de layouts (`app.tsx`)

```ts
layout: (name) => {
  switch (true) {
    case name === 'welcome':          return null;              // sin layout
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

## Superficies del template builder (`layouts/{superficie}/default/`)

Además de los layouts del sistema, existen carpetas de **temas default por superficie** usadas por el template builder (fallback y base de scaffolds, NO exportables):

```
layouts/
├── landing/default/
│   ├── welcome-layout.tsx   ← config: default 'landing'
│   └── welcome-template.tsx
├── dashboard/default/
│   ├── dashboard-layout.tsx ← config: default 'dashboard'
│   └── dashboard-template.tsx
├── forms/default/
│   ├── forms-layout.tsx     ← config: default 'forms'
│   └── forms-template.tsx
└── auth/default/
    ├── auth-layout.tsx      ← config: default 'auth'
    ├── auth-template.tsx
    ├── login.tsx            ← config: default 'login'
    └── logout.tsx           ← config: default 'logout'
```

Estos paths se registran en `config/templates.php::defaults`. No participan en el catálogo público ni en la exportación.

## Layouts públicos

1. **`AppLayout`** (`layouts/app-layout.tsx`) — re-exporta el template de app; usado por páginas autenticadas (dashboard, settings y resto).
2. **`AuthLayout`** (`layouts/auth-layout.tsx`) — re-exporta el template auth simple; usado por páginas `auth/*`.
3. **`SettingsLayout`** (`layouts/settings/layout.tsx`) — sub-nav de ajustes; se monta **además** de `AppLayout` para páginas `settings/*` (layout compuesto): `[AppLayout, SettingsLayout]`.

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