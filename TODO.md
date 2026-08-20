# TODO — Pendientes / Mejoras / Seguridad

Lista de cambios **pendientes** del proyecto, ordenados por prioridad. Este archivo es la fuente de verdad para trabajo futuro.

> **Regla de uso:** cuando se pida revisar/buscar/mejorar algo y **no se resuelva en el momento**, se agrega aquí con su prioridad. Cuando se **resuelva**, se mueve a [`CHANGELOG.md`](./CHANGELOG.md) con la fecha.

## Prioridad

- **P0** — Crítico / bloqueante / seguridad / datos.
- **P1** — Importante, funcionalidad principal o deuda que afecta.
- **P2** — Mejora o deuda menor / deseable.

---

## P0 — Crítico / Seguridad

_(Sin entradas actualmente)_

---

## P1 — Importante / Funcionalidad

- **P1 — Alinear `AGENTS.md` con la arquitectura real.** El `AGENTS.md` referencia `docs/LOGIC_ARCHITECTURE.md`, `docs/STYLE_GUIDE.md`, `docs/SECURITY.md`, `docs/CONTRIBUTING.md`, `docs/ARCHITECTURE.md` y `.ai/rules/` que **no existen** en disco. Decidir si crearlos o corregir el AGENTS.md.
- **P1 — Documentar el dominio de templates en un ficha dedicada.** La arquitectura en el espejo (Actions/Data/Requests/Policy + RBAC) ya está implementada; falta crear `docs/LOGIC_ARCHITECTURE.md` (y, si se deciden, `docs/STYLE_GUIDE.md`, `docs/SECURITY.md`, `docs/CONTRIBUTING.md`) o corregir las referencias del `AGENTS.md`.
- **P1 — Decidir estructura `components/`.** La arquitectura documentada en AGENTS.md espera `components/forms/[contexto]/` y `components/shared/`, pero el código usa `components/` plano + `components/ui/`. Aclarar convención definitiva.
- **P1 — Comercialización B4: templates default inamovibles + borrado.** El template default del proyecto debe ser **no eliminable** (usar `is_undeletable`/policy `delete`) y permitir borrar los templates no-default (UI de borrado + endpoint). Ver también el bug pre-existente de `layouts/system/template.json` (slug `systembuilder` con exports apuntando a `dist/aurora.js`/`.css`), que invalidaría la validación del manifiesto.
- **P1 — Comercialización B5: persistir y aplicar 1 tema por superficie.** La estructura base ya está: páginas por superficie (`pages/{superficie}/{pagina}/{pagina}.tsx`), wrappers `layouts/surfaces/landing-layout.tsx`, `forms-layout.tsx` y `dashboard-layout.tsx` con tema `default`, y dispatcher por prefijo en `app.tsx`. Falta: asociar una superficie (landing/dashboard/forms/auth) con un único tema, persistirlo (settings/tabla o `template.json` de proyecto) e **inyectar dinámicamente** el tema activo en los wrappers de superficie (los formularios `auth/*`/`forms/*` solo reciben el template si el tema expone los roles).
- **P1 — Comercialización B6: render real del tema.** Aplicar el tema seleccionado al proyecto real (no solo en preview/marketplace): el tema instalado reemplaza los layouts/vistas de producción según superficie.
- **P1 — Reorganizar `resources/js/layouts/` a carpetas por superficie.** Mover los layouts del sistema y el tema `default` a `layouts/default/{superficie}/` con naming `{superficie}-template.tsx` (+ `components/`), eliminando `layouts/surfaces/`, `layouts/app-layout.tsx`/`auth-layout.tsx` de la raíz y las carpetas `layouts/app/` y `layouts/auth/`. Fuente de verdad: plan aprobado en conversación. Pasos:
  1. **Estructura objetivo:** `default/app/app-template.tsx` (+`components/app-sidebar-layout.tsx`, `app-header-layout.tsx`), `default/auth/auth-template.tsx` (+`components/auth-simple|card|split-layout.tsx`, `auth-default-layout|login|logout.tsx`), `default/{landing,dashboard,forms}/{superficie}-template.tsx` (+`components/{superficie}-default-layout.tsx`; dashboard además `-sidebar|-topbar`). `settings/layout.tsx` y temas de usuario (`aurora`, `system`) sin cambios.
  2. **Superficies:** fusionar `surfaces/{superficie}-layout.tsx` con `default/{superficie}-default-template.tsx` en un solo `{superficie}-template.tsx`. **app/auth:** mover+renombrar el wrapper (`app-layout.tsx`→`app-template.tsx`, `auth-layout.tsx`→`auth-template.tsx`) conservando re-export hacia `components/`.
  3. **Eliminar:** `layouts/surfaces/`, `app-layout.tsx`, `auth-layout.tsx`, carpetas `app/` y `auth/`, `default/{landing,dashboard,forms}-default-template.tsx` (fusionados), `default/components/` y `default/auth-default-template.tsx` (código muerto, sin consumidores).
  4. **`app.tsx`:** re-apuntar imports a `@/layouts/default/{superficie}/{superficie}-template`. **Imports internos** de cada template a `./components/...`.
  5. **`config/templates.php::defaults`:** paths → `default/{superficie}/components/{...}` + ajustar docblock (el bloque `defaults` no se consume en runtime; actualizar igual como fuente de verdad).
  6. **Docs/reglas:** `docs/design/layouts.md`, `docs/templates/{nomenclature,root-layouts,app-templates,auth-templates,README}.md`, `docs/architecture/frontend.md`, `.ai/rules/{layouts,pages}.md`, `CHANGELOG.md`.
  7. **Verificación:** `npm run types:check`, `npm run lint`, `npm run build`, `php artisan test --compact` (cubre `DashboardPlantillasTest`), `vendor/bin/pint --format agent`, smoke manual (`/dashboard/plantillas`, `/login`, landing, `settings/*`).
  - Nota: `bin/build-template.mjs` y los servicios del builder (Manifest/Scaffold/Export/ResolveTemplateFile/BuildEditorTree) operan solo sobre temas con `template.json`; **no requieren cambios** (tema default no tiene manifiesto).

---

## P2 — Mejoras / Deuda menor

- **P2 — Revisión de seguridad de la aplicación.** Auditar configuraciones (headers, rate limiting por endpoint, sanitización, políticas de autorización) y reforzar antes de exponer dominio de negocio.
- **P2 — Configuraciones de producción.** Revisar `APP_DEBUG`, proveedor de mail, driver de sesión/cache/cola (actualmente `database` en dev) y endurecer `Password::defaults()` ya aplicado.
- **P2 — Endpoint paskeys `well-known`.** La ruta `/.well-known/passkey-endpoints` apunta a `security.edit`; verificar cumplimiento de spec WebAuthn en producción.
- **P2 — Cobertura de tests.** Existen tests del dominio de templates; añadir tests de autorización RBAC por rol (403/200), re-exportación y store de create.

---

## Nota de proceso

- Antes de responder a cualquier petición de revisar/hacer algo en el sistema, **se consultan este `TODO.md` y `CHANGELOG.md`** para tener contexto de pendientes y resueltos.