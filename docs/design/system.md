# Sistema de Diseño

Sistema de diseño basado en **Tailwind CSS v4** con **shadcn/ui** sobre primitivas **Radix UI**. Todos los tokens de color usan valores **oklch()** y se definen como variables CSS mapeadas a semánticos vía el bloque `@theme`.

> Fuente base: `resources/css/app.css`.
> Las anotaciones del proyecto exigen **nunca hardcodear colores o espaciados**; usar los tokens Tailwind.

## Configuración Tailwind v4

```css
@import 'tailwindcss';
@import 'tw-animate-css';

@source '../views';
@source '../../vendor/laravel/framework/.../Pagination/resources/views/*.blade.php';

@custom-variant dark (&:is(.dark *));
```

- **Dark mode:** variante `dark` dirigida por la clase `.dark` en el ancestro (`&:is(.dark *)`), no por `prefers-color-scheme`.
- **Activo por clase:** se alterna en `document.documentElement.classList.toggle('dark', ...)` vía `use-appearance.tsx`.

## Tokens semánticos (`@theme`)

| Categoría | Tokens |
|-----------|--------|
| Tipografía | `--font-sans` → Instrument Sans (Bunny CDN) |
| Radio | `--radius`, `--radius-lg/md/sm` |
| Base | `background`, `foreground` |
| Superficies | `card`, `popover`, `card-foreground`, `popover-foreground` |
| Acción | `primary`, `primary-foreground` |
| Neutros | `secondary`, `muted`, `accent` (+-foreground) |
| Error | `destructive`, `destructive-foreground` |
| Bordes | `border`, `input`, `ring` |
| Charts | `chart-1` … `chart-5` |
| Sidebar | `sidebar`, `sidebar-foreground`, `sidebar-primary`, `sidebar-primary-foreground`, `sidebar-accent`, `sidebar-accent-foreground`, `sidebar-border`, `sidebar-ring` |

## Paleta base (light)

| Token | oklch |
|-------|-------|
| `--background` | `oklch(1 0 0)` |
| `--foreground` | `oklch(0.145 0 0)` |
| `--primary` | `oklch(0.205 0 0)` |
| `--primary-foreground` | `oklch(0.985 0 0)` |
| `--muted` | `oklch(0.97 0 0)` |
| `--destructive` | `oklch(0.577 0.245 27.325)` |
| `--border` | `oklch(0.922 0 0)` |
| `--radius` | `0.625rem` |
| `--sidebar` | `oklch(0.985 0 0)` |

## Paleta dark

| Token | oklch |
|-------|-------|
| `--background` / `--card` / `--popover` | `oklch(0.145 0 0)` |
| `--foreground` | `oklch(0.985 0 0)` |
| `--primary` | `oklch(0.985 0 0)` |
| `--primary-foreground` | `oklch(0.205 0 0)` |
| `--secondary` / `--muted` / `--accent` | `oklch(0.269 0 0)` |
| `--border` / `--input` | `oklch(0.269 0 0)` |
| `--sidebar` | `oklch(0.205 0 0)` |
| `--destructive` | `oklch(0.396 0.141 25.723)` |

Nota: en dark, `sidebar-primary` y su foregound son ambos claros/oscuros invertidos según el caso; el token base de sidebar es casi negro.

## Capas base

```css
@layer base {
  *  { @apply border-border; }
  body { @apply bg-background text-foreground; }
}
```

## Gestión del tema (`hooks/use-appearance.tsx`)

- `initializeTheme()` — aplica el tema al arrancar.
- `useAppearance()` — suscripción vía `useSyncExternalStore`.
- Persistencia: `localStorage` + cookie SSR `appearance` (excluida de cifrado).
- El toggle se propaga añadiendo/quitando la clase `dark` en `<html>`.

## Utilidades de clases

- `lib/utils.ts` exporta `cn()` (mezcla `clsx` + `tailwind-merge`).
- `cva` (`class-variance-authority`) define las variantes de los componentes `ui/`.

## Convenciones

- Usar tokens semánticos (`bg-card`, `text-muted-foreground`, `border-border`), nunca colores hex.
- Orden de clases gestionado por `prettier-plugin-tailwindcss`.
- Iconos vía `lucide-react` (a menudo envueltos en `components/ui/icon.tsx`).
- Animaciones CSS de `tw-animate-css`.