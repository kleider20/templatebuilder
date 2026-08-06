# Templates de la Aplicación (App Shell)

Templates bajo `layouts/app/` que definen el **shell** de las páginas autenticadas. Un solo template está activo; el otro queda definido pero sin usar.

## `app/app-sidebar-layout.tsx` — TEMPLATE ACTIVO

Structure del shell con sidebar:

```tsx
<AppShell variant="sidebar">
  <AppSidebar />
  <AppContent variant="sidebar" className="overflow-x-hidden">
    <AppSidebarHeader breadcrumbs={breadcrumbs} />
    {children}
  </AppContent>
</AppShell>
```

- Exportada por defecto; recibe `AppLayoutProps` (`children`, `breadcrumbs`).
- Es el template que `app-layout.tsx` re-exporta como `AppLayoutTemplate`.

## `app/app-header-layout.tsx` — template alternativo (NO usado)

- Define el mismo shell pero con `variant="header"`.
- No está conectado a ningún wrapper público; sirve como alternativa de layout de barra superior.

## Piezas que componen el shell (`components/`)

- **`app-shell.tsx`** — Contenedor raíz del shell. Lee `usePage().props.sidebarOpen` y admite `variant` (sidebar/header). Controla el estado de apertura (incluido móvil vía `Sheet`).
- **`app-content.tsx`** — Área de contenido; admite `variant` para ajustar el layout.
- **`app-sidebar.tsx`** — Sidebar de navegación (usa el sistema `ui/sidebar.tsx`).
- **`app-sidebar-header.tsx`** — Cabecera de la zona de contenido (breadcrumbs).
- **`app-header.tsx`** — Variante de cabecera del shell `header`.

## Componentes auxiliares de navegación

- `nav-main.tsx` — Navegación principal.
- `nav-user.tsx` / `user-menu-content.tsx` / `user-info.tsx` — Usuario y menú.
- `nav-footer.tsx` — Pie de la navegación.
- `breadcrumbs.tsx` — Render de las migas de pan.

## Cómo cambiar el shell global

1. Editar `layouts/app/app-sidebar-layout.tsx`, o
2. Hacer que `layouts/app-layout.tsx` re-exporte otro template (p.ej. `app-header-layout`).

## Notas

- Tokens del sidebar: definidos en [`../design/system.md`](../design/system.md) (familia `sidebar*`).
- El estado `sidebarOpen` se comparte entre servidor/cliente vía props de Inertia y cookie `sidebar_state` (no cifrada).