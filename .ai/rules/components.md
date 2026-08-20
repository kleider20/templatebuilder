---
paths:
  - resources/js/components/template-preview-surface.tsx
  - 'resources/js/components/**'
---

# Components

## Preview de temas renderiza fuente, no el bundle dist
El preview de superficies NUNCA debe volver a importar el bundle remoto dist/{slug}.js por URL: quedaba colgado en "Cargando superficie…" (sin timeout ni error boundary útil, dependía del import map/MIME). Renderiza el código fuente: ruta del rol en manifest.components → `import.meta.glob('/resources/js/layouts/**/*.tsx')` indexado por `/resources/js/layouts/{path}`. El ErrorBoundary debe envolver el Suspense. El CSS del tema sigue viniendo de dist/{slug}.css.

## lazy() de superficies se construye a nivel de módulo (nunca en el render)
Los componentes lazy del preview (import.meta.glob sobre layouts/**/*.tsx) se construyen UNA vez a nivel de módulo en un mapa themeComponents. Crearlos dentro del render/useMemo cambia su identidad entre renders y, con React Compiler + StrictMode, el Suspense queda atascado en "Cargando superficie…" para siempre.

## El preview compone la superficie a partir de TODOS los roles del manifest
DynamicTemplate (template-preview-surface.tsx) resuelve todos los roles de manifest.components[surface] (header/topmenu/sidebar/footer/login/template) y arma el shell según el vocabulario de config/templates.php: header/topmenu arriba, sidebar al costado (dashboard), template como contenido (el template ya envuelve su layout) y footer abajo. En auth, login se renderiza como contenido del template. No renderizar solo el rol template: si el layout del tema no incluye header/footer (como los temas scaffold/aurora), el preview quedaría en blanco. Cada pieza va envuelta en su propio ErrorBoundary.

## Organización de componentes: default/ vs temas comerciales
Los componentes de templates por defecto (internos, no eliminables) viven en resources/js/components/default/{slug}/. Los de templates comerciales/nuevos en resources/js/components/{slug}/. El manifiesto requiere una carpeta por template; si no hay componentes, se crea un .gitkeep para que la carpeta exista (TemplateExportService la incluye en el ZIP). Los roles de superficie del builder (LandingRole, DashboardRole) se mantienen en layouts/{slug}/components/roles/, NO en components/.

## Crear un tema desde plantilla: componentes reutilizables van en components/{slug}
Cuando el usuario pide crear un tema y muestra una plantilla, clasificar cada archivo. Un componente REUTILIZABLE que NO es indispensable para el template/layout (no es header, footer, topmenu, sidebar ni la estructura de la página) va en resources/js/components/{slug}/ (tema comercial) o resources/js/components/default/{slug}/ (tema por defecto). Si la carpeta del tema queda vacía, crear un .gitkeep. Lo estructural SIEMPRE va en layouts/{slug}/ (ver .ai/rules/layouts.md).
