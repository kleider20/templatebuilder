---
paths:
  - database/seeders/RolePermissionSeeder.php
---

# Seeders

## Seeder tolerante a $this->command null
Los seeders usan $this->command?->info() (null-safe) porque en tests (RefreshDatabase + ejecución manual del seeder) $this->command es null y ->info() lanza error. Seeder debe ser invocable en tests.
