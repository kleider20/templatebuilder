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

---

## P2 — Mejoras / Deuda menor

- **P2 — Revisión de seguridad de la aplicación.** Auditar configuraciones (headers, rate limiting por endpoint, sanitización, políticas de autorización) y reforzar antes de exponer dominio de negocio.
- **P2 — Configuraciones de producción.** Revisar `APP_DEBUG`, proveedor de mail, driver de sesión/cache/cola (actualmente `database` en dev) y endurecer `Password::defaults()` ya aplicado.
- **P2 — Endpoint paskeys `well-known`.** La ruta `/.well-known/passkey-endpoints` apunta a `security.edit`; verificar cumplimiento de spec WebAuthn en producción.
- **P2 — Cobertura de tests.** Existen tests del dominio de templates; añadir tests de autorización RBAC por rol (403/200), re-exportación y store de create.

---

## Nota de proceso

- Antes de responder a cualquier petición de revisar/hacer algo en el sistema, **se consultan este `TODO.md` y `CHANGELOG.md`** para tener contexto de pendientes y resueltos.