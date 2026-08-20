import { lazy } from 'react';
import type React from 'react';

/**
 * Loaders lazy de los temas, construidos UNA sola vez a nivel de módulo (por
 * ruta). Crearlos dentro del render (aunque sea en un useMemo) cambia su
 * identidad entre renders y, con React Compiler + StrictMode, el Suspense
 * queda atascado en el fallback para siempre.
 */
const themeLoaders = import.meta.glob('/resources/js/layouts/**/*.tsx');

export const themeComponents: Record<string, React.LazyExoticComponent<React.ComponentType<any>>> =
    Object.fromEntries(
        Object.entries(themeLoaders).map(([key, loader]) => [
            key,
            lazy(() =>
                loader().then((mod) => {
                    const comp = (mod as { default?: React.ComponentType }).default;

                    return {
                        default: comp ?? (() => null),
                    };
                }),
            ),
        ]),
    );

/**
 * Componente lazy de un rol del tema (ruta relativa a resources/js/layouts/),
 * o null si no existe.
 */
export function themeComponent(relPath: string | undefined | null) {
    if (!relPath) {
        return null;
    }

    return themeComponents[`/resources/js/layouts/${relPath}`] ?? null;
}