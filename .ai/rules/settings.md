---
paths:
  - app/Actions/Settings/DeleteProfileAction.php
---

# Settings

## Borrar remember_token antes de eliminar cuenta
Al eliminar una cuenta hay que hacer $user->setRememberToken(null) antes de delete(); si no, Auth::logout() posterior re-inserta el usuario (el guard cicla el remember token).
