import { lazy, Suspense, useMemo, useState, useEffect, Component } from 'react';
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
    const [reloadKey, setReloadKey] = useState(0);

    useEffect(() => {
        setReloadKey((k) => k + 1);
    }, [surface]);

    useEffect(() => {
        if (typeof window !== 'undefined') {
            window.__TPL_SETTINGS__ = settings;
            window.dispatchEvent(new Event('tpl:settings-changed'));
        }
    }, [settings]);

    return (
        <Suspense fallback={<PreviewLoading />}>
            <DynamicTemplate
                key={`${surface}-${reloadKey}`}
                slug={slug}
                surface={surface}
            />
        </Suspense>
    );
}

function DynamicTemplate({ slug, surface }: { slug: string; surface: string }) {
    const ComponentRef = useMemo(() => {
        const cacheBust = import.meta.env.DEV ? `?t=${Date.now()}` : '';
        const moduleUrl = `/templates/${slug}/dist/${slug}.js${cacheBust}`;

        return lazy(() =>
            import(/* @vite-ignore */ moduleUrl).then((mod) => {
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
            }),
        );
    }, [slug, surface]);

    return <ComponentRef />;
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