# Templates de Autenticación

Templates bajo `layouts/auth/` para las páginas `auth/*`. Un solo template está activo; los otros dos están definidos pero sin usar.

## `auth/auth-simple-layout.tsx` — TEMPLATE ACTIVO

Card de autenticación centrada sobre `bg-background`.

- Es lo que `auth-layout.tsx` re-exporta como wrapper público.
- Soporta `title` y `description` (provistos por cada página auth vía `page.layout`).
- Algunas páginas (p.ej. `two-factor-challenge`) actualizan dinámicamente estos textos con `setLayoutProps`.

## `auth/auth-card-layout.tsx` — definido, NO usado

- Variante que encierra el formulario dentro de un componente `Card`.
- Disponible como alternativa de presentación (más "encerrada" que blur card flotante).

## `auth/auth-split-layout.tsx` — definido, NO usado

- Variante **split-screen**: una zona de marca/marketing y otra con el formulario.
- Pensada para landing-style auth si se necesita más personalidad de marca.

## Páginas que usan el template auth

- `pages/auth/login.tsx`
- `pages/auth/register.tsx`
- `pages/auth/forgot-password.tsx`
- `pages/auth/reset-password.tsx`
- `pages/auth/confirm-password.tsx`
- `pages/auth/verify-email.tsx`
- `pages/auth/two-factor-challenge.tsx`

Todas se montan con `AuthLayout` porque su nombre empieza por `auth/`.

## Cómo cambiar el template de auth

Editar `layouts/auth-layout.tsx` para re-exportar otro template (`auth-card-layout` o `auth-split-layout`).

## Notas

- El componente de registro reutiliza `PasswordInput` e integra passkeys (`@laravel/passkeys`).
- El layout auth se alterna por el despachador de `app.tsx` (`name.startsWith('auth/')`).