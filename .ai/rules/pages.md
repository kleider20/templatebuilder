---
paths:
  - 'resources/js/pages/**'
---

# Pages

## Páginas por superficie: crear {superficie}/{pagina}/{pagina}.tsx según tipo
Toda página se crea con la estructura {superficie}/{pagina}/{pagina}.tsx (nombre Inertia = path completo). Clasificar por TIPO: públicas sin login → landing/ (ej. landing/welcome/welcome.tsx); formularios → forms/; login/register/auth → auth/ (ej. auth/login/login.tsx); páginas autenticadas de la app → dashboard/ (ej. dashboard/plantillas/plantillas.tsx). settings/ y templates/ (builder) quedan fuera de las superficies. Las páginas NO definen layout: lo asigna el dispatcher resources/js/app.tsx por superficie (landing/* → LandingLayout que aplica el template landing activo, forms/* → FormsLayout, dashboard/* → DashboardLayout que aplica el template dashboard del tema default, auth/* → AuthLayout). Así, cambiar el template de landing aplica automáticamente a todas las páginas landing/. Al crear una página nueva en landing/ se le aplica el template landing sin tocar la página.

## El catálogo de plantillas es dashboard/plantillas (templates/index eliminado)
El catálogo de plantillas NO vive en templates/index (ruta y página eliminadas; GET /templates responde 405). Es dashboard.plantillas: `DashboardPlantillasController` renderiza `dashboard/plantillas/plantillas` con el payload completo (templates, surfaces, canCreate, canViewAll) y se muestra con el chrome del dashboard. Los fallbacks del editor redirigen a dashboard.plantillas.
