# Documentación del Proyecto — Índice Maestro

> **Este archivo es la puerta de entrada a toda la documentación.**
> El agente (IA) DEBE leer este archivo y la(s) ficha(s) correspondiente(s) al cambio pedido cada vez que el usuario pida crear, corregir o modificar cualquier parte del proyecto (estructura de carpetas, tecnologías, skills, controladores, tablas, rutas, frontend, diseño, etc.).

## Flujo obligatorio (regla global)

1. **Leer solo lo que corresponde**: el usuario pide un cambio → el agente identifica el/los documento(s) de la tabla de mapeo y lee **únicamente** esos (no toda la documentación), para no gastar tokens.
2. **Confirmar antes de tocar código**: se presenta el cambio propuesto y se solicita la aprobación del usuario.
3. **Aplicar el cambio** una vez confirmado.
4. **Actualizar el documento** que corresponda con el cambio realizado (estructuras, tecnologías, controladores, tablas, rutas, versiones, etc.).
5. **Verificar** (pint, tests, typecheck) cuando aplique.

## Inventario de archivos

| Ruta | Propósito |
|------|-----------|
| [`ARCHITECTURE.md`](./ARCHITECTURE.md) | Resumen ejecutivo, stack, flujo de datos, estructura raíz, índices |
| [`architecture/backend.md`](./architecture/backend.md) | Backend: `app/`, modelos, controllers, Services, validación, BD, tests |
| [`architecture/frontend.md`](./architecture/frontend.md) | Frontend: `resources/js/`, páginas, componentes, hooks, tipos, Wayfinder |
| [`architecture/routing.md`](./architecture/routing.md) | Enrutado: `web.php`, `settings.php`, `templates.php`, console, rate limiters |
| [`architecture/specifications.md`](./architecture/specifications.md) | Versiones exactas instaladas (Composer + npm), scripts, config clave |
| [`design/system.md`](./design/system.md) | Sistema de diseño: Tailwind v4, tokens, paletas, dark mode |
| [`design/components.md`](./design/components.md) | Catálogo de componentes `ui/` y de app |
| [`design/layouts.md`](./design/layouts.md) | Jerarquía de layouts / despachador de `app.tsx` |
| [`templates/README.md`](./templates/README.md) | Sistema de templates de UI + **template builder** |
| [`templates/nomenclature.md`](./templates/nomenclature.md) | Convenciones de nombres / jerarquía Wrapper–Template |
| [`templates/app-templates.md`](./templates/app-templates.md) | Templates de app shell (sidebar/header) |
| [`templates/auth-templates.md`](./templates/auth-templates.md) | Templates de auth (simple/card/split) |
| [`templates/root-layouts.md`](./templates/root-layouts.md) | Wrappers públicos (AppLayout/AuthLayout/SettingsLayout) |
| [`../CHANGELOG.md`](../CHANGELOG.md) | Cambios resueltos |
| [`../TODO.md`](../TODO.md) | Cambios pendientes por prioridad |

## Tabla de mapeo: cambio → documento(s) a leer/actualizar

| Tipo de cambio | Documento(s) |
|----------------|--------------|
| Backend: controllers, modelos, Services, comandos, requests, migraciones/tablas, tests back | [`architecture/backend.md`](./architecture/backend.md) |
| Frontend: páginas, componentes, hooks, tipos, layouts | [`architecture/frontend.md`](./architecture/frontend.md) + [`design/components.md`](./design/components.md) |
| Rutas (nuevas rutas, grupos, middleware, rate limiters, console) | [`architecture/routing.md`](./architecture/routing.md) |
| Nuevas tecnologías / dependencias / scripts / versiones | [`architecture/specifications.md`](./architecture/specifications.md) |
| Sistema de diseño: tokens, paletas, dark mode, Tailwind | [`design/system.md`](./design/system.md) |
| Layouts / despachador de `app.tsx` | [`design/layouts.md`](./design/layouts.md) + [`templates/root-layouts.md`](./templates/root-layouts.md) |
| Templates de app/auth shell | [`templates/app-templates.md`](./templates/app-templates.md) + [`templates/auth-templates.md`](./templates/auth-templates.md) |
| **Template builder**: superficies, temas default, manifiestos, export/paridad, builder | [`templates/README.md`](./templates/README.md) + [`config/templates.php`](../config/templates.php) |
| Vista general / estructura global | [`ARCHITECTURE.md`](./ARCHITECTURE.md) |
| Skills / nuevas capacidades del agente | [`../AGENTS.md`](../AGENTS.md) |

## Reglas de cumplimiento

- **Los documentos son la fuente de verdad.** Si el código no cumple lo documentado, el agente debe **informar la discrepancia/incongruencia al usuario** para que decida, y **proponer la mejor recomendación alineada al proyecto**.
- Cada vez que se aplique una corrección de estructura o un cambio, se actualiza el documento correspondiente en el **mismo turno** (no en lotes posteriores).
- El agente responde **siempre en español latino** salvo que el usuario indique lo contrario.