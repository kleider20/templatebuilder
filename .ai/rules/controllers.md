---
paths:
  - app/Http/Controllers/Controller.php
---

# Controllers

## Controller base usa AuthorizesRequests
El Controller base abstracto debe usar el trait AuthorizesRequests, de lo contrario $this->authorize() dentro de los controllers lanza "Call to undefined method ... authorize()". Esto dio 403 espurios en tests.
