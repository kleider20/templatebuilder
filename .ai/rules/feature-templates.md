---
paths:
  - 'tests/Feature/Templates/**'
---

# Feature Templates

## Usar templateAdminUser() para endpoints protegidos
Los tests de templates que tocan rutas autenticadas (editor/manifest/preview/export/thumbnail) deben usar el helper global templateAdminUser() de tests/Pest.php (seedea RolePermissionSeeder y asigna rol 'admin'). Un usuario sin permisos recibe 403 porque TemplatePolicy exige view_templates/manage_templates. Los themes scaffoldeados no tienen fila en `templates`, así que el entidad de autorización firstOrNew tiene user_id null.
