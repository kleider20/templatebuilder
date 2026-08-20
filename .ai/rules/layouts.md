---
paths:
  - 'resources/js/layouts/**'
---

# Layouts

## Chrome del dashboard de la app se edita en layouts/default/components/dashboard/ vía DashboardLayout
El dashboard del sistema (páginas dashboard/*) usa el template dashboard del tema default a través del wrapper de superficie surfaces/dashboard-layout.tsx (dispatcher en app.tsx). Para cambiar la UI del dashboard real de la app se editan los componentes del tema default: layouts/default/components/dashboard/dashboard-default-layout.tsx (compone sidebar+topbar+main), dashboard-default-sidebar.tsx y dashboard-default-topbar.tsx. El sidebar/topbar usan rutas reales del proyecto (dashboard, plantillas, security, profile, logout) y tokens de app.css, NO bg-surface/text-content (exclusivos de CSS de tema). El sidebar NO incluye ítem "Editor" a /templates: el catálogo es "Plantillas" (dashboard.plantillas) y el editor se alcanza desde las tarjetas del catálogo.

## Temas: carpeta hermana en layouts/ con home=slug
Cada tema exportable es una carpeta hermana en resources/js/layouts/{slug}/ (home=slug). Templates {tipo}-{slug}-template.tsx en la raíz; layouts/roles en components/{tipo}/{tipo}-{slug}-{rol}.tsx. Los temas default no exportables viven consolidados en layouts/default/ con slug "default" (p.ej. landing-default-template.tsx). No usar ya {superficie}/templates/{slug}/: TemplateManifestService, TemplateScaffoldService, TemplateExportService, ResolveTemplateFileAction, BuildEditorTreeAction y bin/build-template.mjs asumen esta estructura.

## Crear un tema desde plantilla: estructural (header/footer/menú/template) va en layouts/{slug}
Cuando el usuario pide "crea un template con este componente" y muestra una plantilla, clasificar cada archivo por su rol en la página. Los archivos ESTRUCTURALES e indispensables del tema (header, footer, topmenu, sidebar, template, layout, login/logout) van SIEMPRE dentro de la carpeta del tema en layouts/: template en la raíz layouts/{slug}/{tipo}-{slug}-template.tsx y roles en layouts/{slug}/components/{tipo}/{tipo}-{slug}-{rol}.tsx. NUNCA moverlos a resources/js/components/. Los roles de superficie del builder se mantienen en layouts/{slug}/components/roles/.
