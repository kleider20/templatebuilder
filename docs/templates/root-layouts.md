# Root Layouts (Wrappers)

Los **wrappers** son los layouts públicos que `app.tsx` importa. Re-exportan un template concreto del directorio homólogo de layout.

## `AppLayout` — `layouts/app-layout.tsx`

Wrapper para páginas autenticadas (dashboard, settings y resto).

- Re-exporta `AppLayoutTemplate` desde `layouts/app/app-sidebar-layout.tsx`.
- Recibe `AppLayoutProps` (`children`, `breadcrumbs`).

## `AuthLayout` — `layouts/auth-layout.tsx`

Wrapper para páginas de autenticación (`auth/*`).

- Re-exporta `auth-simple-layout`.
- Las páginas auth aportan `title` y `description` vía `page.layout = { title, description }`.

## `SettingsLayout` — `layouts/settings/layout.tsx`

Sub-layout de la sección de ajustes.

- Renderiza una **sub-navegación** (Perfil / Seguridad / Apariencia) que se resalta según la URL actual (`useCurrentUrl`).
- Se monta **en cadena** con `AppLayout` para páginas `settings/*`:
  ```ts
  layout: (name) => (name.startsWith('settings/') ? [AppLayout, SettingsLayout] : AppLayout)
  ```
- Su contenido es la propia página (settings/profile, settings/security, settings/appearance).

## Despachador global — `resources/js/app.tsx`

```ts
const layouts = {
  welcome:   null,
  auth:      AuthLayout,
  settings:  [AppLayout, SettingsLayout],
  default:   AppLayout,
};
```

Usa `name.startsWith('auth/')` y `name.startsWith('settings/')` como comodines; cualquier otra página cae en `AppLayout`. Las páginas del **template builder** (`templates/*`: `templates/create`, `templates/editor`, `templates/preview`, `templates/versions`, `templates/marketplace`) caen en el `default` → `AppLayout` (salvo `templates/preview` y `templates/marketplace`, sin layout). El catálogo ya no es `templates/index`: vive en `dashboard/plantillas` (ver mapa en [`../templates/README.md`](../templates/README.md)).

## Notas

- Los breadcrumbs se inyectan desde cada página (`page.layout = { breadcrumbs }`).
- No hay definición de layout en la propia `app.tsx` para `welcome` (landing sin chrome).

## Detalle de templates

- Estructura del shell → [`app-templates.md`](./app-templates.md).
- Variantes auth → [`auth-templates.md`](./auth-templates.md).