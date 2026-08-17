# Nomenclatura y Jerarquía de Templates

Convenciones para crear y organizar layouts/templates y componentes en el frontend.

## Jerarquía Wrapper / Template

- **Wrapper** (público, importado en `app.tsx`): vive en `layouts/` raíz — `app-layout.tsx`, `auth-layout.tsx`, `settings/layout.tsx`. Solo re-exporta un template y por norma no contiene estructura visual.
- **Template** (estructura concreta): vive en `layouts/{app,auth}/...` — `app-sidebar-layout.tsx`, `app-header-layout.tsx`, `auth-simple-layout.tsx`, `auth-card-layout.tsx`, `auth-split-layout.tsx`. Arma el shell/card real.
- **Composición**: layouts encadenados (`[AppLayout, SettingsLayout]`) para páginas que combinan chrome + sub-nav.

## Nombrado de archivos

- **Kebab-case** en todo `resources/js/` — páginas, componentes, layouts, hooks, rutas:
  - `two-factor-setup-modal.tsx`, `app-sidebar-header.tsx`, `auth-simple-layout.tsx`.
- Excepción de **PascalCase** solo en `actions/` (refleja clases PHP generadas por Wayfinder).
- Componentes `ui/` de una palabra: `button.tsx`, `card.tsx`.

## Temas del template builder

Cada tema es una carpeta hermana en `resources/js/layouts/{slug}/` (su `home`), con naming
`{tipo}-{slug}-{rol}.tsx`:

- **Templates** en la raíz: `landing-aurora-template.tsx`, `dashboard-aurora-template.tsx`.
- **Layouts/roles** en `components/{tipo}/`: `landing-aurora-layout.tsx`,
  `landing-aurora-header.tsx`, `dashboard-aurora-sidebar.tsx`, `auth-aurora-login.tsx`.

Los **temas default** (no exportables) viven en `resources/js/layouts/default/` con el mismo
patrón usando `{slug} = default` (`landing-default-template.tsx`,
`default/components/landing/landing-default-layout.tsx`).

## Exportaciones

- **`ui/` components** → exportación **nombrada** (`export function Button`).
- **Top-level app components** → **default export** por norma; los wrappers multi-componente (`.tsx` que exportan varios: `AppContent`, `AppShell`, `AppSidebar`) usan named exports.
- **Layouts** → **default export**.

## Buena práctica al crear un template

1. Crear el template en `layouts/{area}/...` con kebab-case y default export.
2. Si debe ser público, re-exportarlo desde el wrapper de `layouts/` (raíz).
3. Si es Layout de página Inertia, apuntarlo en el despachador de `app.tsx`.
4. Mantener kebab-case y respetar los tokens de diseño.

## Comprobar antes de añadir un layout

- ¿Existe ya un template de app (`sidebar`/`header`) que se pueda reutilizar?
- ¿El wrapper apropiado (`AppLayout`/`AuthLayout`) ya ofrece lo necesario?
- ¿Debe componerse con `SettingsLayout` u otro layout existente en vez de crear uno nuevo?