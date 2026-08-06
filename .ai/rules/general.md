---
paths:
  - '**'
  - eslint.config.js
  - .env
---

# General

## Nunca salir de la raíz del proyecto (basheo seguro)
El agente nunca debe cd fuera de C:\laragon\www\templatebuilder ni apuntar a C:\laragon\www\ (../) u otras rutas. Editar archivos solo con Read/Edit/Write de rutas absolutas de la raíz; comandos con workdir (nunca cadenas cd && ...). Prohibido 2>/dev/null o rutas fake (/workspace /c /mnt) para ocultar errores. Sólo salir de la raíz si el usuario lo pide.

## Lint: excluir dist de temas, stubs y scripts bin
eslint.config.js: los bundles generados de temas (resources/js/**/dist/**), los stubs (*.stub) y los scripts Node de bin/ (*.mjs con globals.node) están configurados para no ensuciar el lint. El preview remoto (template-preview-surface.tsx) carga bundles dist externos vía lazy dinámico; usa disables puntuales con justificación react-hooks/purity y react-hooks/static-components porque el módulo por slug no puede declararse a nivel de módulo.

## APP_KEY/runtime web desalineado: limpiar config:clear y reiniciar worker
Si el navegador ve errores `MissingAppKeyException` (500) o `APP_ENV=production` mientras el CLI/tinker lee `APP_ENV=local` correcto, es una caché de config vieja del worker web de Apache/Laragon. Tras ajustar la APP_KEY o el .env, ejecutar `php artisan config:clear && php artisan route:clear && php artisan cache:clear && php artisan view:clear` y reiniciar el servicio PHP/Apache de Laragon, luego recargar la pestaña. Verificar con `curl -H "Cookie: laravel_session=fake" <url>` (con cookie trae el error; sin cookie siempre da 200).
