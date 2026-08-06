---
paths:
  - 'resources/js/pages/templates/**'
---

# Templates

## Wayfinder helper names for template routes
The export route helper is named `exportMethod` (not `export`, a reserved word) in resources/js/routes/templates. The preview GET is `show`, exported from resources/js/routes/templates/preview (submodule), not from routes/templates top-level. When regenerating routes, run `php artisan wayfinder:generate --with-form` (the `--with-form` flag is required or generated routes lose the `.form()` variant that existing pages rely on).
