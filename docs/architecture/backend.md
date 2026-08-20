# Backend (Laravel)

Capa de servidor de la aplicación. Incluye el scaffold de autenticación (starter kit) y, desde el CONTRATO v7, el **dominio del template builder** (servicios de manifiesto/scaffold/export, controladores de gestión y modelo de versiones).

## Estructura de `app/`

```
app/
├── Actions/
│   ├── Fortify/
│   │   ├── CreateNewUser.php          # Contrato CreatesNewUsers (Fortify) + rol 'user'
│   │   ├── ResetUserPassword.php      # Contrato ResetsUserPasswords (Fortify)
│   │   ├── UpdateUserProfileInformation.php  # Contrato UpdatesUserProfileInformation
│   │   └── UpdateUserPassword.php     # Contrato UpdatesUserPasswords (Fortify)
│   ├── Templates/
│   │   ├── CatalogTemplatesAction.php   # catálogo con scope propios/"ver todos" + canCreate/canViewAll
│   │   ├── CreateTemplateAction.php     # scaffold + registro Template (dueño)
│   │   ├── BuildEditorTreeAction.php    # árbol del editor desde manifiesto
│   │   ├── ResolveTemplateFileAction.php# resolución segura de rutas (anti path traversal)
│   │   ├── ReadTemplateFileAction.php    # lee archivo del tema
│   │   ├── SaveTemplateFileAction.php    # guarda archivo validado en allowlist
│   │   ├── UpdateManifestAction.php      # actualiza settings/theme/pricing del manifiesto
│   │   ├── UploadThumbnailAction.php     # sube preview → {home}/thumbnails/...
│   │   ├── ExportTemplateAction.php      # export con bump de versión + TemplateVersion
│   │   └── MarketplaceTemplatesAction.php# listado público del marketplace (default + registrados)
│   └── Settings/
│       ├── UpdateProfileAction.php    # PATCH de perfil
│       ├── DeleteProfileAction.php    # borra cuenta (inborrables: is_undeletable)
│       └── UpdatePasswordAction.php   # PUT de contraseña
├── Concerns/
│   ├── PasswordValidationRules.php    # Traits: passwordRules()/currentPasswordRules()
│   └── ProfileValidationRules.php     # Traits: profileRules()/nameRules()/emailRules()
├── Console/
│   └── Commands/
│       ├── TemplateMakeCommand.php    # Scaffold de un tema bajo layouts/{slug}
│       ├── TemplateImportCommand.php  # `template:import {source}` — instala un tema (ZIP o slug)
│       └── UserPromoteCommand.php     # `user:promote` — asigna rol super_admin
├── Data/
│   ├── Templates/
│   │   ├── TemplateManifestData.php   # (spatie/laravel-data) fromManifest/toManifestArray
│   │   ├── TemplateSummaryData.php    # item del catálogo
│   │   ├── TemplateVersionData.php
│   │   ├── TemplateTreeNodeData.php
│   │   ├── TemplateFileData.php
│   │   └── TemplatePreviewData.php
│   └── Settings/
│       ├── ProfileData.php
│       ├── SecurityData.php
│       └── AppearanceData.php
├── Http/
│   ├── Controllers/
│   │   ├── Controller.php             # Base abstracta + AuthorizesRequests
│   │   ├── Templates/
│   │   │   ├── TemplateController.php     # index/create/store
│   │   │   ├── TemplateEditorController.php  # show/update + updateManifest + uploadThumbnail
│   │   │   ├── TemplateExportController.php  # index/store/download versions
│   │   │   ├── TemplatePreviewController.php  # show + data (paleta validada)
│   │   │   ├── TemplateMarketplaceController.php # marketplace público (sin auth) + download
│   │   │   └── TemplateViewController.php # paridad: show + asset
│   │   └── Settings/
│   │       ├── ProfileController.php  # edit / update / destroy
│   │       └── SecurityController.php # edit / update (password)
│   ├── Middleware/
│   │   ├── HandleAppearance.php
│   │   └── HandleInertiaRequests.php  # comparte roles + permissions en Inertia
│   └── Requests/
│       ├── Settings/
│       │   ├── PasswordUpdateRequest.php
│       │   ├── ProfileDeleteRequest.php
│       │   ├── ProfileUpdateRequest.php
│       │   └── TwoFactorAuthenticationRequest.php
│       └── Templates/
│           ├── StoreTemplateRequest.php
│           ├── UpdateTemplateRequest.php     # content (max 200000)
│           ├── UpdateTemplateManifestRequest.php
│           ├── UploadThumbnailRequest.php
│           └── ExportTemplateRequest.php
├── Models/
│   ├── User.php                  # HasRoles + relación templates() + is_test_user/is_undeletable
│   ├── Template.php              # catálogo con slug/name/user_id + versiones
│   ├── TemplateVersion.php
│   ├── Role.php                  # extiende Spatie\Permission + RoleMetadata
│   ├── Permission.php            # extiende Spatie\Permission + PermissionMetadata
│   ├── RoleMetadata.php
│   └── PermissionMetadata.php
├── Policies/
│   └── TemplatePolicy.php        # view/viewAny/create/update/export/delete/viewAll (RBAC)
├── Services/
│   └── Templates/
│       ├── TemplateManifestService.php  # parse/valida manifiestos → TemplateManifestData
│       ├── TemplateScaffoldService.php  # crea estructura de carpetas de un tema
│       ├── TemplateExportService.php    # export(ZIP, $version) + nextVersion() + TemplateVersion
│       └── TemplateImportService.php    # importa ZIP/slug → layouts/{slug} + registra + rebuild
└── Providers/
    ├── AppServiceProvider.php    # Gate::policy(Template) + Gate::before (super_admin)
    └── FortifyServiceProvider.php
```

> Desde el CONTRATO de arquitectura en espejo, las Autores **Actions** encapsulan la lógica, los **FormRequests** validan (nunca inline en controller), los **Data objects** (`spatie/laravel-data`) transforman colecciones a Inertia y la **política RBAC** centraliza la autorización sobre templates.

## Modelos

- **`app/Models/User.php`** — modelo de usuario.
  - Extiende `Illuminate\Foundation\Auth\User` e implementa `Laravel\Fortify\Contracts\PasskeyUser`.
  - Traits: `HasFactory`, `Notifiable`, `PasskeyAuthenticatable`, `TwoFactorAuthenticatable`, y **`HasRoles`** (spatie/laravel-permission).
  - Fillable: `name`, `email`, `password`, `is_test_user`, `is_undeletable`.
  - Casts: `email_verified_at` (datetime), `password` (hashed), `is_test_user`/`is_undeletable` (boolean).
  - Relación `templates()` (HasMany `Template`, `user_id`). `roles()`, `permissions()` y `getAllPermissions()` vienen de `HasRoles`.
- **`app/Models/Template.php`** — catálogo de temas.
  - Persiste en tabla `templates`.
  - Columnas: `id`, `slug`, `name`, `description`, `version`, `user_id` (dueño) y timestamps.
- **`app/Models/TemplateVersion.php`** — versión exportada de un tema.
  - Persiste en tabla `template_versions` (ver "Base de datos").
  - Columnas: `id`, `slug`, `version`, `manifest` (text), `zip_path`, `checksum`, `user_id`.
- **`Role` / `Permission`** — extienden los modelos de spatie; cada uno con metadata (`RoleMetadata` / `PermissionMetadata`: display_name, description, category, is_critical, color, icon, level, users_count, permissions_count, is_protected, is_fallback…).

## RBAC (Roles y Permisos)

- Paquetes: `spatie/laravel-permission` y `spatie/laravel-data`.
- Seeders encadenados en `DatabaseSeeder`: `RolePermissionSeeder` → `RoleMetadataSeeder` → `PermissionMetadataSeeder` → `TestSuperAdminSeeder`.
- Roles: `super_admin`, `admin`, `editor`, `user`, `base`.
  - `super_admin`: `Gate::before` le otorga todo (`AppServiceProvider`). Se sincroniza con **todos** los permisos.
  - `admin`: recibe todos los permisos + los «critical» (`manage_templates`, `view_all_templates`, `manage_users`, `manage_roles`, `manage_permissions`, `manage_database`…).
  - `editor` y `user`: permisos de lectura básica (`view_dashboard`, `view_templates`, `view_versions`).
  - `base`: rol de «fallback» con `view_dashboard`.
  - `user`: rol por defecto al registrarse (`CreateNewUser`).
- Comando `user:promote {email}` → asigna rol `super_admin`.
- `HandleInertiaRequests` comparte `roles` y `permissions` del usuario logueado.

## Autorización (`TemplatePolicy`)

- Registrada en `AppServiceProvider` con `Gate::policy(Template::class, TemplatePolicy::class)`.
- Conductas clave:
  - `viewAny` → ``view_templates``.
  - `create` → ``create_templates``.
  - `view`/`update`/`export`/`delete` → dueño con su permiso, **o** permiso de alcance global (`view_all_templates`, `manage_templates`, `delete_all_templates`).
  - `viewAll` → ``view_all_templates``.
- Los controllers usan `Template::firstOrNew(['slug' => …])` como entidad de autorización para temas del filesystem.

## Controllers

- **`Settings\ProfileController`** → `edit` (GET render vía `ProfileData`), `update` (PATCH → `UpdateProfileAction`), `destroy` (DELETE → `DeleteProfileAction`).
- **`Settings\SecurityController`** → `edit` (GET render vía `SecurityData`), `update` (PUT → `UpdatePasswordAction`).
- El resto de rutas (auth) de las registra Fortify automáticamente.
- **`Templates\TemplateController`** → `index` (catálogo vía `CatalogTemplatesAction`, expone `canCreate`/`canViewAll`), `create`, `store` (→ `CreateTemplateAction`).
- **`Templates\TemplateEditorController`** → `show` (árbol + active file vía `BuildEditorTreeAction`/`ReadTemplateFileAction`), `update` (→ `SaveTemplateFileAction`), `updateManifest` (→ `UpdateManifestAction`), `uploadThumbnail` (→ `UploadThumbnailAction`).
- **`Templates\TemplateExportController`** → `index` (listado de versiones), `store` (→ `ExportTemplateAction` con `nextVersion()`), `download`.
- **`Templates\TemplateMarketplaceController`** → `index` (marketplace público sin auth → `MarketplaceTemplatesAction`, expone default + registrados con pricing), `download` (ZIP de export de un tema; 404 si no tiene `TemplateVersion`).
- **`Templates\TemplatePreviewController`** → `show` (render preview, responde con `Cache-Control: no-store` vía el middleware `NoStore` para evitar que el navegador sirva HTML viejo del preview), `data` (valida `palette` con `Rule::in` sobre las paletas del tema).
- **`Templates\TemplateViewController`** → `show` (paridad `{slug}/{dir}/{file}`), `asset` (paridad `assets/{path}`). Sirve artefactos compilados.

## Services (`app/Services/Templates`)

- **`TemplateManifestService`** — parsea y valida los manifiestos de tema; resuelve rutas seguras (evita path traversal y devuelve `TemplateManifestData`). Valida el bloque `pricing` (`type` free/premium; `premium` exige `price > 0`; `currency` por defecto `USD`).
- **`TemplateScaffoldService`** — crea la estructura de carpetas/archivos de un tema nuevo bajo `layouts/{slug}` (templates en la raíz y layouts/roles en `components/{superficie}/`). Genera `pricing: {type: free}` por defecto.
- **`TemplateExportService`** — funciona con `$version` (export a versión concreta) y `nextVersion()` (bump de patch 1.0.0 → 1.0.1); empaqueta ZIP y genera el registro `TemplateVersion`. Incluye en el ZIP `template.json`, la carpeta del tema con `dist/` y los stubs comerciales renderizados (`install.php` + `README.md` desde `resources/stubs/commercial/`).
- **`TemplateImportService`** — `importFromZip($zip, $force, $userId)` extrae en `storage/app/template-imports`, valida el manifiesto contra una base temporal y copia al home real (lanzar `RuntimeException` si el tema ya existe sin `--force`); `importFromSlug($slug, $userId)` valida el tema ya extraído. `install()` registra la propiedad en `templates` **solo si** `$userId !== null` (columna NOT NULL) y `ensureBuilt()` recompila `dist/` con `node bin/build-template.mjs {slug}` si el ZIP no trae artefactos.

## Actions / Concerns

- **Actions** del dominio de Templates y Settings (ver árbol `app/Actions/`). Son la única capa con lógica de negocio; los controllers se mantienen delgados.
- **Concerns**: `PasswordValidationRules` y `ProfileValidationRules` (traits de validación reutilizables).

## Validación (FormRequests)

Todas las validaciones de ajustes viven en `app/Http/Requests/Settings/`, se usan por inyección de tipo en los controllers:
- `PasswordUpdateRequest` — actualización de contraseña.
- `ProfileUpdateRequest` — actualización de perfil (nombre/email).
- `ProfileDeleteRequest` — borrado de cuenta.
- `TwoFactorAuthenticationRequest` — flujo de 2FA.

## Actions / Concerns

- Las **Actions** `CreateNewUser` y `ResetUserPassword` implementan contratos de Fortify y centralizan la validación de creación/reseteo.
- Los **Concerns** son traits reutilizables de reglas de validación (`PasswordValidationRules`, `ProfileValidationRules`).
- **No existen Data Objects (DTO) ni Services.**

## Providers y bootstrap

- `bootstrap/providers.php`: `AppServiceProvider`, `FortifyServiceProvider`.
- `bootstrap/app.php`: middleware `HandleAppearance`, `HandleInertiaRequests`, `AddLinkHeadersForPreloadedAssets` sobre el grupo `web`; cookies `appearance` y `sidebar_state` excluidas de cifrado; health route `/up`; renderizado JSON para peticiones `api/*` con expectativas JSON (path muerto, no hay rutas api).
- `AppServiceProvider`: `CarbonImmutable` por defecto; `DB::prohibitDestructiveCommands` en producción; endurece `Password::defaults()` en producción (mín. 12, mayúsculas/letras/números/símbolos, no comprometida).
- `FortifyServiceProvider`: vincula Actions, define vistas Inertia para auth, rate limiters `two-factor` (5/min), `login` (5/min), `passkeys` (10/min).

## Base de datos (SQLite)

Migraciones en `database/migrations`:

1. `0001_01_01_000000_create_users_table` → `users`, `password_reset_tokens`, `sessions`.
2. `0001_01_01_000001_create_cache_table` → `cache`, `cache_locks`.
3. `0001_01_01_000002_create_jobs_table` → `jobs`, `job_batches`, `failed_jobs`.
4. `2024_01_01_000000_create_passkeys_table` → `passkeys` (credenciales WebAuthn).
5. `2025_08_14_170933_add_two_factor_columns_to_users_table` → columnas 2FA en `users`.
6. `2026_08_05_000255_create_template_versions_table` → `template_versions`.
7. `2026_08_06_192635_create_role_metadata_table` → `role_metadata`.
8. `2026_08_06_192636_create_permission_metadata_table` → `permission_metadata`.
9. `2026_08_06_192637_add_test_user_columns_to_users_table` → columnas `is_test_user`, `is_undeletable` en `users`.
10. `2026_08_06_192638_create_templates_table` → `templates` (catálogo, con `slug` único y `user_id`).

> Las tablas de roles/permisos (`roles`, `permissions`, `model_has_roles`, `model_has_permissions`, `role_has_permissions`) las crea el paquete `spatie/laravel-permission` (publicadas por su propio proveedor).

### Tabla `template_versions`

| Columna | Tipo | Notas |
|---------|------|-------|
| `id` | integer (PK) | auto-increment |
| `slug` | varchar | index no único |
| `version` | varchar | única con `slug` |
| `manifest` | text | manifiesto del tema |
| `zip_path` | varchar | ruta del ZIP de export |
| `checksum` | varchar | hash de integridad |
| `user_id` | integer | autor del export |
| `created_at` / `updated_at` | datetime | timestamps |

Índice único: `(slug, version)`.

### Tabla `templates`

| Columna | Tipo | Notas |
|---------|------|-------|
| `id` | integer | PK |
| `slug` | varchar | único |
| `name` | varchar | título |
| `description` | text/nullable | |
| `version` | varchar | versión actual |
| `user_id` | FK `users.id` | dueño del tema |
| timestamps | datetime | |

### Factories
- `database/factories/UserFactory.php` — estados por defecto, `unverified()`, `withTwoFactor()`.

### Seeders
- `database/seeders/DatabaseSeeder.php` — encadena los seeders RBAC + crea `Test User`.
- `RolePermissionSeeder` — crea roles/permisos y asignaciones (ver sección RBAC).
- `RoleMetadataSeeder` / `PermissionMetadataSeeder` — metadata de roles/permisos.
- `TestSuperAdminSeeder` — superadmin@templatebuilder.test y admin@templatebuilder.test.

## Tests backend (Pest)

- `tests/Feature/Auth/` — authentication, email verification, password confirmation/reset, registration, two-factor challenge, verification notification.
- `tests/Feature/` — DashboardTest, ExampleTest.
- `tests/Feature/Settings/` — ProfileUpdateTest, SecurityTest.
- `tests/Feature/Templates/` — `EditorTest`, `EndpointsTest` (401/403/422), `ExportTest`, `ManifestTest`, `ImportTest`, `ParityTest` (template builder).