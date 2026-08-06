import { lazy, Suspense, useMemo, useEffect, useState, Component } from 'react';
import React from 'react';
import { Card, CardContent } from '@/components/ui/card';

type PreviewSurfaceProps = {
    slug: string;
    surface: string;
    settings: Record<string, string | boolean | number>;
};

/**
 * Carga el bundle dist/{slug}.js del tema (parity URL) y renderiza el rol
 * "template" (o "login") de la superficie seleccionada. React se comparte vía
 * import map del host.
 */
export default function PreviewSurface({
    slug,
    surface,
    settings,
}: PreviewSurfaceProps) {
    useEffect(() => {
        if (typeof window !== 'undefined') {
            window.__TPL_SETTINGS__ = settings;
            window.dispatchEvent(new Event('tpl:settings-changed'));
        }
    }, [settings]);

    return (
        <Suspense fallback={<PreviewLoading />}>
            <DynamicTemplate key={surface} slug={slug} surface={surface} />
        </Suspense>
    );
}

function DynamicTemplate({ slug, surface }: { slug: string; surface: string }) {
    const [cacheBust] = useState(() => (import.meta.env.DEV ? `?t=${Date.now()}` : ''));

    const cssUrl = useMemo(
        () => `${window.location.origin}/templates/${slug}/dist/${slug}.css${cacheBust}`,
        [slug, cacheBust],
    );

    // `lazy()` debe crearse por slug/surface; como el módulo remoto es dinámico
    // no puede declararse a nivel de módulo. El moduleUrl usa origen absoluto
    // para funcionar en dev (donde el módulo corre desde el Vite dev server pero
    // los bundles de temas viven en resources/js/layouts y los sirve Laravel).
    const ComponentRef = useMemo(() => {
        const moduleUrl = `${window.location.origin}/templates/${slug}/dist/${slug}.js${cacheBust}`;

        return lazy(() => {
            return import(/* @vite-ignore */ `${moduleUrl}`).then((mod) => {
                const map = mod.components ?? mod.default;
                const role = surface === 'auth' ? 'login' : 'template';
                const comp = map?.[surface]?.[role] ?? map?.[surface]?.template;

                const Wrapped = comp
                    ? () => (
                          <ErrorBoundaryHOC surface={surface}>
                              {React.createElement(comp)}
                          </ErrorBoundaryHOC>
                      )
                    : () => <PreviewEmpty surface={surface} />;

                return { default: Wrapped };
            });
        });
    }, [slug, surface, cacheBust]);

    return (
        <>
            <link rel="stylesheet" href={cssUrl} />
            {/* eslint-disable-next-line react-hooks/static-components -- componente remoto dinámico */}
            <ComponentRef />
        </>
    );
}

function ErrorBoundaryHOC({ children, surface }: { children: React.ReactNode; surface: string }) {
    return (
        <ErrorBoundary surface={surface}>{children}</ErrorBoundary>
    );
}

class ErrorBoundary extends Component<any, { error: Error | null }> {
    constructor(props: any) {
        super(props);
        this.state = { error: null };
    }

    static getDerivedStateFromError(error: Error) {
        return { error };
    }

    render() {
        if (this.state.error) {
            return <PreviewError message={this.state.error.message} />;
        }

        return this.props.children;
    }
}

function PreviewLoading() {
    return (
        <div className="grid min-h-[60vh] place-items-center">
            <Card>
                <CardContent className="py-10 text-sm text-muted-foreground">
                    Cargando superficie…
                </CardContent>
            </Card>
        </div>
    );
}

function PreviewEmpty({ surface }: { surface: string }) {
    return (
        <div className="p-8">
            <Card>
                <CardContent className="py-10 text-sm text-muted-foreground">
                    No hay template para la superficie "{surface}" en este tema.
                </CardContent>
            </Card>
        </div>
    );
}

function PreviewError({ message }: { message: string }) {
    return (
        <div className="p-8">
            <Card>
                <CardContent className="py-10 text-sm text-red-600">
                    No se pudo cargar el template: {message}
                </CardContent>
            </Card>
        </div>
    );
}

declare global {
    interface Window {
        __TPL_SETTINGS__?: Record<string, string | boolean | number>;
    }
}