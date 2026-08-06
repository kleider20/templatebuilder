# Catálogo de Componentes

Los componentes React se dividen en dos grupos: **primitivas `ui/`** (shadcn/Radix) y **componentes de dominio/app** (top-level). Todos en **kebab-case**.

## Componentes primitivos — `components/ui/` (26)

Exportaciones **nombradas** (`export function Button`). Basados en Radix UI + `cva`.

| Componente | Uso |
|-----------|-----|
| `avatar.tsx` | Foto/avatars |
| `alert.tsx` | Alertas contextuales |
| `badge.tsx` | Etiquetas/badges |
| `breadcrumb.tsx` | Migas de pan |
| `button.tsx` | Botón con variantes (`variant`, `size`) |
| `card.tsx` | Tarjetas de contenido |
| `checkbox.tsx` | Checkbox (Radix) |
| `collapsible.tsx` | Panel plegable |
| `dialog.tsx` | Modal accesible |
| `dropdown-menu.tsx` | Menú desplegable (Radix) |
| `icon.tsx` | Envoltorio de iconos Lucide |
| `input.tsx` | Campo de texto |
| `input-otp.tsx` | Campo OTP (input-otp) |
| `label.tsx` | Label accesible |
| `navigation-menu.tsx` | Menú de navegación (Radix) |
| `placeholder-pattern.tsx` | Patrón decorativo/placeholder |
| `select.tsx` | Select (Radix) |
| `separator.tsx` | Separador (Radix) |
| `sheet.tsx` | Panel lateral (Sidebar móvil) |
| `sidebar.tsx` | Sistema de sidebar shadcn |
| `skeleton.tsx` | Esqueleto de carga |
| `sonner.tsx` | Integración <Toaster> (sonner) |
| `spinner.tsx` | Indicador de carga |
| `toggle.tsx` | Toggle (Radix) |
| `toggle-group.tsx` | Grupo de toggles (Radix) |
| `tooltip.tsx` | Tooltip (Radix) |

## Componentes de app — `components/` top-level (29)

Exportaciones en su mayoría **default**, salvo wrappers multi-componente (`AppContent`, `AppShell`, `AppSidebar`) que usan named exports.

### Shell / Navegación
- `app-shell.tsx` — Shell con variante `sidebar`/`header` (lee `usePage().props.sidebarOpen`).
- `app-content.tsx` — Área de contenido dentro del shell.
- `app-header.tsx` / `app-sidebar-header.tsx` — Cabeceras del shell.
- `app-sidebar.tsx` — Sidebar de la aplicación.
- `app-logo.tsx` / `app-logo-icon.tsx` — Logo.
- `breadcrumbs.tsx` — Render de migas.
- `nav-main.tsx` / `nav-user.tsx` / `nav-footer.tsx` — Secciones de navegación.
- `user-menu-content.tsx` / `user-info.tsx` — Menú y datos del usuario.

### Auth / Passkeys / 2FA
- `passkey-register.tsx` / `passkey-verify.tsx` / `passkey-item.tsx` — Flujo WebAuthn.
- `manage-passkeys.tsx` — Gestión de passkeys.
- `manage-two-factor.tsx` — Gestión de 2FA.
- `two-factor-setup-modal.tsx` — Modal de setup 2FA.
- `two-factor-recovery-codes.tsx` — Códigos de recuperación.

### Formularios / UI de página
- `appearance-tabs.tsx` — Tabs de apariencia (tema).
- `password-input.tsx` — Input de contraseña con toggle de visibilidad.
- `input-error.tsx` — Mensaje de error de campo.
- `alert-error.tsx` — Alerta de error general.
- `heading.tsx` — Encabezado/título de página.
- `text-link.tsx` — Botón con estilo de enlace.
- `delete-user.tsx` — Confirmación de borrado de cuenta.

### Template builder
- `manifest-editor.tsx` — Editor del manifiesto (modal JSON, `settings`/`theme`), guarda vía `templates.manifest.update`.
- `template-preview-surface.tsx` — Superficie de render del preview (multi-superficie), emite `tpl:settings-changed`. Carga el bundle `dist/{slug}.js` remoto con URL absoluta de `window.location.origin` (funciona en dev) e inyecta el `<link>` al CSS del tema `dist/{slug}.css` (`manifest.exports.styles`), del cual dependen las utilidades del tema (`bg-primary`, `bg-surface`, `--tpl-*`).

## Convenciones

- **kebab-case** en todos los archivos (unificado con layouts/pages/hooks).
- Componentes `ui/` accesibles: Radix + `asChild` + `cva` variantes.
- Los componentes de dominio combinan primitivas `ui/` con lógica de Inertia/forms.
- Importación vía alias `@/components/...`.

## Vínculos

- Sistema de diseño y tokens → [`system.md`](./system.md).
- Layouts → [`layouts.md`](./layouts.md) y [`../templates/README.md`](../templates/README.md).
- Convención de nombres → [`../templates/nomenclature.md`](../templates/nomenclature.md).