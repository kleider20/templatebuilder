---
paths:
  - bin/build-template.mjs
---

# Bin

## CSS del tema autocontenido: tokens semánticos shadcn mapeados a roles
Los componentes del tema (aurora/default/stubs) usan clases shadcn (`text-content`, `border-border`, `bg-card`, `border-input`, `text-muted-foreground`). buildCssEntry() debe mapear ESOS tokens semánticos a los roles del tema en el @theme inline (`content`/`foreground`→text, `card`/`input`/`muted`→surface, `border`→text 15%, `muted-foreground`→text 60%, `primary-foreground`→background, `ring`→primary); si no, dist/{slug}.css no genera esas utilidades y el tema se ve sin estilos. Tras cambiar el mapeo, regenerar con `node bin/build-template.mjs {slug}`.
