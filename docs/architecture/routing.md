# Enrutado

Laravel 13 registra rutas por grupos (`routes/web.php`, `routes/settings.php`, `routes/templates.php`, `routes/console.php`). **No existe `routes/api.php` ni `routes/auth.php`**: las rutas de autenticación las registra automáticamente **Laravel Fortify** (guard `web`, feature-driven).

## Resumen de rutas

| Método | URI | Handler | Nombre | Middleware |
|--------|-----|---------|--------|------------|
| ANY | `/` | Inertia `welcome` | `home` | web |
| GET | `/dashboard` | Inertia `dashboard` | `dashboard` | web, auth, verified |
| GET | `/settings` | redirect → `/settings/profile` | — | web, auth |
| GET | `/settings/profile` | `ProfileController@edit` | `profile.edit` | web, auth |
| PATCH | `/settings/profile` | `ProfileController@update` | `profile.update` | web, auth |
| DELETE | `/settings/profile` | `ProfileController@destroy` | `profile.destroy` | web, auth, verified |
| GET | `/settings/security` | `SecurityController@edit` | `security.edit` | web, auth, verified, RequirePassword |
| PUT | `/settings/password` | `SecurityController@update` | `user-password.update` | web, auth, verified, throttle:6,1 |
| ANY | `/settings/appearance` | Inertia `settings/appearance` | `appearance.edit` | web, auth, verified |
| GET | `/.well-known/passkey-endpoints` | closure JSON | `well-known.passkeys` | web |

## Template builder (`routes/templates.php`)

Archivo propio del dominio del builder. Las rutas de gestión se registran **antes** que la ruta de paridad para que `{slug}/{dir}/{file}` no las capture. Todo el grupo de gestión usa middleware `auth` + `verified` y autorización por `TemplatePolicy` (permisos `view_templates`, `manage_templates`, etc.).

### Gestión (middleware `auth, verified`, prefijo `templates`)

| Método | URI | Handler | Nombre |
|--------|-----|---------|--------|
| GET | `/templates/create` | `TemplateController@create` | `templates.create` |
| POST | `/templates` | `TemplateController@store` | `templates.store` |
| GET | `/templates/versions` | `TemplateExportController@index` | `templates.versions.index` |
| GET | `/templates/versions/{version}/download` | `TemplateExportController@download` | `templates.versions.download` |
| GET | `/templates/{slug}/editor` | `TemplateEditorController@show` | `templates.editor` |
| PUT | `/templates/{slug}/editor` | `TemplateEditorController@update` | `templates.editor.update` |
| PUT | `/templates/{slug}/manifest` | `TemplateEditorController@updateManifest` | `templates.manifest.update` |
| POST | `/templates/{slug}/thumbnail` | `TemplateEditorController@uploadThumbnail` | `templates.thumbnail.update` |
| GET | `/templates/{slug}/preview` | `TemplatePreviewController@show` | `templates.preview.show` |
| POST | `/templates/{slug}/preview` | `TemplatePreviewController@data` | `templates.preview` |
| POST | `/templates/{slug}/export` | `TemplateExportController@store` | `templates.export` |

> El catálogo (`GET /templates`, `templates.index`) **se eliminó**: el catálogo vive en `dashboard.plantillas` (`GET /dashboard/plantillas`, `DashboardPlantillasController`, chrome del dashboard). `GET /templates` responde 405 (solo queda el `POST /` de `store`). Los fallbacks del editor redirigen a `dashboard.plantillas`.

> `TemplateManifestEditorController` se fusionó dentro de `TemplateEditorController@updateManifest`.
>
> `GET /templates/{slug}/preview` aplica el middleware `NoStore` (responde `Cache-Control: no-store`) para que el navegador nunca sirva HTML viejo del preview con URLs de build.

### Marketplace (público, sin auth, prefijo `templates`)

| Método | URI | Handler | Nombre |
|--------|-----|---------|--------|
| GET | `/marketplace` | `TemplateMarketplaceController@index` | `marketplace` |
| GET | `/marketplace/{slug}/download` | `TemplateMarketplaceController@download` | `marketplace.download` |

> El marketplace es **público** (sin middleware `auth`): lista los temas default + registrados con su pricing y permite descargar el ZIP de export (404 si el tema no tiene `TemplateVersion`). `app.tsx` devuelve `null` como layout para `templates/marketplace`.

### Paridad de URLs (públicas, canónicas, idénticas a proyectos finales)

| Método | URI | Handler | Nombre | Constraint |
|--------|-----|---------|--------|------------|
| GET | `/templates/{slug}/{dir}/{file}` | `TemplateViewController@show` | `templates.parity.show` | `dir ∈ dist\|assets\|thumbnails` |
| GET | `/templates/{slug}/assets/{path}` | `TemplateViewController@asset` | `templates.parity.asset` | `path` = `.*` |

## Rutas registradas por Fortify

Rutas de auth con nombres estándar (referenciadas por Wayfinder `@/routes` y `@/actions`):

- `login`, `login.store`, `logout`, `register`, `register.store`
- `password.request`, `password.email`, `password.reset`, `password.update`
- `verification.notice`, `verification.verify`, `verification.send`
- `two-factor.login`, `password.confirm`, `user-password.update`

Features habilitados en `config/fortify.php`: registro, reset de contraseñas, verificación de email, 2FA (confirmación + re-confirmación de contraseña), passkeys (con confirmación de contraseña). `home` → `/dashboard`.

## Rate limiters (`FortifyServiceProvider`)

| Nombre | Límite |
|--------|--------|
| `two-factor` | 5/min |
| `login` | 5/min |
| `passkeys` | 10/min |
| `user-password.update` | throttle:6,1 (en ruta) |

## Middleware

Registrados globalmente en el grupo `web` (`bootstrap/app.php`):
- `HandleAppearance` — sincroniza tema.
- `HandleInertiaRequests` — inyecta props compartidas (`name`, `auth`, `sidebarOpen`).
- `AddLinkHeadersForPreloadedAssets` — preload de assets.

Cookies excluidas de cifrado: `appearance`, `sidebar_state`.

## Console

`routes/console.php` solo define el comando stock `inspire`. Comandos personalizados (`app/Console/Commands`):
- `TemplateMakeCommand` (scaffold de un tema bajo `layouts/{slug}`).
- `TemplateImportCommand` (`template:import {source} {--force} {--user=}` — instala un tema desde un ZIP de export o desde un slug ya extraído; opciones: `--force` sobrescribe el tema existente, `--user` registra la propiedad en `templates`).