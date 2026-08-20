# {{ name }}

> Tema para el Template Builder — v{{ version }}

**Slug:** `{{ slug }}`
**Autor:** {{ author }}
**Descripción:** {{ description }}
**Precio:** {{ pricing }}
**Superficies:** {{ types }}

## Requisitos

{{ requires }}

## Instalación

1. Copiá la carpeta `{{ slug }}/` dentro de `resources/js/layouts/` de tu proyecto,
   o simplemente ejecutá el instalador incluido desde la raíz del proyecto:

   ```
   php install.php
   ```

2. Desde la raíz del proyecto, validá y registrá el tema:

   ```
   php artisan template:import {{ slug }}
   ```

3. Abrí `/templates` en tu proyecto para ver el tema en el catálogo y previsualizarlo.

## Uso

- El tema aporta las superficies: **{{ types }}**.
- Podés elegirlo por superficie (landing, dashboard, forms, auth) desde la gestión de
  temas de tu proyecto.
- Si el tema no incluye la carpeta `dist/` compilada, generala con:

  ```
  node bin/build-template.mjs {{ slug }}
  ```

## Notas

- Requiere React 19 y Tailwind v4 (ver sección Requisitos).
- Este README y `install.php` se generan al exportar el tema desde el builder.