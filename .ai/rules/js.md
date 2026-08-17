---
paths:
  - resources/js/app.tsx
---

# Js

## Preview del tema se renderiza full-screen sin AppLayout
`templates/preview` devuelve `null` en el despachador de layouts (como `welcome`): el preview aplica el template del tema a toda la pantalla sin el chrome del builder. Los controles (pestañas de superficie, paletas, apariencia, opciones) viven en un panel flotante de preview.tsx, no en un layout.
