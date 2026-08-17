---
paths:
  - 'resources/js/layouts/**'
---

# Layouts

## Temas: carpeta hermana en layouts/ con home=slug
Cada tema exportable es una carpeta hermana en resources/js/layouts/{slug}/ (home=slug). Templates {tipo}-{slug}-template.tsx en la raíz; layouts/roles en components/{tipo}/{tipo}-{slug}-{rol}.tsx. Los temas default no exportables viven consolidados en layouts/default/ con slug "default" (p.ej. landing-default-template.tsx). No usar ya {superficie}/templates/{slug}/: TemplateManifestService, TemplateScaffoldService, TemplateExportService, ResolveTemplateFileAction, BuildEditorTreeAction y bin/build-template.mjs asumen esta estructura.
