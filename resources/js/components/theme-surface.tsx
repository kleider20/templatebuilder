import { usePage } from '@inertiajs/react';
import { Component, Suspense, useMemo, useState } from 'react';
import type React from 'react';
import { themeComponent } from '@/lib/theme-loaders';
import type { ThemeApplicationInfo, ThemeApplications } from '@/types';

type ThemeSurfaceProps = {
    surface: keyof ThemeApplications;
    fallback: React.ReactNode;
    children: React.ReactNode;
};

/**
 * Aplica el tema activo persistido a una superficie en producción: carga el rol
 * `template` del tema (lazy, vía theme-loaders) y envuelve las páginas de la
 * superficie. Si no hay tema aplicado o el tema no soporta la superficie,
 * renderiza el `fallback` (template default del proyecto). Inyecta el CSS del
 * tema (`dist/{slug}.css`) para resolver las utilidades y paletas del tema.
 */
export default function ThemeSurface({ surface, fallback, children }: ThemeSurfaceProps) {
    const { themeApplications } = usePage<{ themeApplications: ThemeApplications }>().props;
    const active = themeApplications[surface];

    if (!active || !active.components[surface]?.template) {
        return <>{fallback}</>;
    }

    return (
        <ErrorBoundary surface={surface}>
            <Suspense fallback={<div className="min-h-screen bg-background" />}>
                <ThemeTemplate surface={surface} active={active}>
                    {children}
                </ThemeTemplate>
            </Suspense>
        </ErrorBoundary>
    );
}

function ThemeTemplate({
    surface,
    active,
    children,
}: {
    surface: string;
    active: ThemeApplicationInfo;
    children: React.ReactNode;
}) {
    const [cacheBust] = useState(() => (import.meta.env.DEV ? `?t=${Date.now()}` : ''));

    const cssUrl = useMemo(
        () => `${window.location.origin}/templates/${active.slug}/dist/${active.slug}.css${cacheBust}`,
        [active.slug, cacheBust],
    );

    const TemplateRef = themeComponent(active.components[surface]?.template);

    if (!TemplateRef) {
        return <>{children}</>;
    }

    return (
        <>
            <link rel="stylesheet" href={cssUrl} />
            <TemplateRef>{children}</TemplateRef>
        </>
    );
}

class ErrorBoundary extends Component<
    { children: React.ReactNode; surface: string },
    { error: Error | null }
> {
    constructor(props: { children: React.ReactNode; surface: string }) {
        super(props);
        this.state = { error: null };
    }

    static getDerivedStateFromError(error: Error) {
        return { error };
    }

    render() {
        if (this.state.error) {
            return (
                <div className="flex min-h-screen items-center justify-center bg-background p-8 text-sm text-red-600">
                    No se pudo cargar el tema para la superficie "{this.props.surface}":{' '}
                    {this.state.error.message}
                </div>
            );
        }

        return this.props.children;
    }
}