# Seguridad de raíz / Basheo (OBLIGATORIO)

- El agente **nunca** debe `cd` fuera de `C:\laragon\www\templatebuilder` ni apuntar comandos a `C:\laragon\www\` (`../`) u otras rutas del disco.
- Toda edición/creación de archivos usa `Read`/`Edit`/`Write` con rutas **absolutas dentro de la raíz**.
- Para comandos de terminal se usa el parámetro `workdir` del tool; prohibido `cd ... && ...`.
- Prohibido usar `2>/dev/null`, `2>&1 | head` o paths estilo `/workspace/...`, `/c/...`, `/mnt/...` para ocultar/omitir errores de ruta.
- Sólo se puede salir de la raíz si el usuario lo pide explícitamente.