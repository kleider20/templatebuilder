# Backend (Laravel)

Capa de servidor de la aplicación. Incluye el scaffold de autenticación (starter kit) y, desde el CONTRATO v7, el **dominio del template builder** (servicios de manifiesto/scaffold/export, controladores de gestión y modelo de versiones).

## Estructura de `app/`

```
app/
├── Actions/
│   └── Fortify/
│       ├── CreateNewUser.php          # Contrato CreatesNewUsers (Fortify)
│       └── ResetUserPassword.php      # Contrato ResetsUserPasswords (Fortify)
├── Concerns/
│   ├── PasswordValidationRules.php    # Traits: passwordRules()/currentPasswordRules()
│   └── ProfileValidationRules.php     # Traits: profileRules()/nameRules()/emailRules()
├── Console/
│   └── Commands/
│       └── TemplateMakeCommand.php    # Scaffold de un tema bajo layouts/{superficie}/{slug}
├── Http/
│   ├── Controllers/
│   │   ├── Controller.php             # Base abstracta
│   │   ├── TemplateController.php     # catálogo + create + store
│   │   ├── TemplateEditorController.php  # editor: show/update archivos + árbol
│   │   ├── TemplateExportController.php  # export: index/store/download versions
│   │   ├── TemplateManifestEditorController.php  # update de manifiesto
│   │   ├── TemplatePreviewController.php  # preview: show + data (paleta validada)
│   │   ├── TemplateViewController.php # paridad: show (dist/assets) + asset
│   │   └── Settings/
│   │       ├── ProfileController.php  # edit / update / destroy
│   │       └── SecurityController.php # edit / update (password)
│   ├── Middleware/
│   │   ├── HandleAppearance.php
│   │   └── HandleInertiaRequests.php
│   └── Requests/
│       └── Settings/
│           ├── PasswordUpdateRequest.php
│           ├── ProfileDeleteRequest.php
│           ├── ProfileUpdateRequest.php
│           └── TwoFactorAuthenticationRequest.php
├── Models/
│   ├── User.php
│   └── TemplateVersion.php
├── Services/
│   └── Templates/
│       ├── TemplateManifestService.php  # parse/valida manifiestos, resuelve rutas seguras
│       ├── TemplateScaffoldService.php  # crea estructura de carpetas de un tema
│       ├── TemplateExportService.php    # genera ZIP de export + TemplateVersion
│       ├── TemplateManifestDto.php      # DTO tipado de manifiesto
│       └── TemplateComponentDto.php     # DTO de componente
└── Providers/
    ├── AppServiceProvider.php
    └── FortifyServiceProvider.php
```

> Los directorios ausentes del patrón "espejo" aspiracional (`app/Data`, `app/Enums`, `app/Jobs`, `app/Mail`, `app/Notifications`, `app/Observers`, `app/Policies`) siguen sin existir; la lógica de negocio del builder vive en `app/Services/Templates/`.

## Modelos

- **`app/Models/User.php`** — modelo de usuario.
  - Extiende `Illuminate\Foundation\Auth\User` e implementa `Laravel\Fortify\Contracts\PasskeyUser`.
  - Traits: `HasFactory`, `Notifiable`, `PasskeyAuthenticatable`, `TwoFactorAuthenticatable`.
  - Fillable: `name`, `email`, `password`.
  - Hidden: `password`, `two_factor_secret`, `two_factor_recovery_codes`, `remember_token`.
  - Casts: `email_verified_at` (datetime), `password` (hashed), `two_factor_confirmed_at` (datetime).
  - **Sin relaciones** definidas en el modelo; `passkeys()` y 2FA vienen de traits de paquete.
- **`app/Models/TemplateVersion.php`** — versión exportada de un tema.
  - Persiste en tabla `template_versions` (ver "Base de datos").
  - Columnas: `id`, `slug`, `version`, `manifest` (text), `zip_path`, `checksum`, `user_id`.

## Controllers

- **`Settings\ProfileController`** → `edit` (GET render), `update` (PATCH fill+save), `destroy` (DELETE).
- **`Settings\SecurityController`** → `edit` (GET render), `update` (PUT password).
- El resto de rutas (auth) las registra Fortify automáticamente.
- **`TemplateController`** → `index` (catálogo), `create`, `store` (crea tema vía `TemplateScaffoldService`). `thumbnailUrl()` genera la URL canónica `templates/{slug}/thumbnails/{file}` y devuelve `null` si el archivo no existe (el frontend muestra placeholder).
- **`TemplateEditorController`** → `show` (editor + árbol de archivos), `update` (guardar archivo), `uploadThumbnail` (subir imagen preview → `{home}/thumbnails/preview.<ext>` y actualiza `thumbnail` en `template.json`). Contiene resolución segura de rutas (`resolveSafePath`) y recorrido del árbol (`tree`, `walk`, `readSafeFile`).
- **`TemplateExportController`** → `index` (listado de versiones), `store` (genera ZIP y crea `TemplateVersion`), `download`.
- **`TemplateManifestEditorController`** → `update` (guardar manifiesto JSON).
- **`TemplatePreviewController`** → `show` (render preview), `data` (valida `palette` con `Rule::in` sobre las paletas del tema).
- **`TemplateViewController`** → `show` (paridad `{slug}/{dir}/{file}`), `asset` (paridad `assets/{path}`). Sirve artefactos compilados.

## Services (`app/Services/Templates`)

- **`TemplateManifestService`** — parsea y valida los manifiestos de tema; resuelve rutas seguras (evita path traversal), devuelve DTOs.
- **`TemplateScaffoldService`** — crea la estructura de carpetas/archivos de un tema nuevo bajo `layouts/{superficie}/{slug}`.
- **`TemplateExportService`** — compila (vía `bin/build-template.mjs` según el caso), empaqueta en ZIP y genera el registro `TemplateVersion`.
- **`TemplateManifestDto` / `TemplateComponentDto`** — DTOs tipados (patrón espejo).

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

### Factories
- `database/factories/UserFactory.php` — estados por defecto, `unverified()`, `withTwoFactor()`.

### Seeders
- `database/seeders/DatabaseSeeder.php` — crea un `Test User` (`test@example.com`).

## Tests backend (Pest)

- `tests/Feature/Auth/` — authentication, email verification, password confirmation/reset, registration, two-factor challenge, verification notification.
- `tests/Feature/` — DashboardTest, ExampleTest.
- `tests/Feature/Settings/` — ProfileUpdateTest, SecurityTest.
- `tests/Feature/Templates/` — `EditorTest`, `EndpointsTest` (401/403/422), `ExportTest`, `ManifestTest`, `ParityTest` (template builder).