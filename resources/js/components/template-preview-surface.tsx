import { Component, Suspense, useEffect, useMemo, useState } from 'react';
import React from 'react';
import { Card, CardContent } from '@/components/ui/card';
import { themeComponent } from '@/lib/theme-loaders';
import type { TemplateManifest } from '@/types';

type PreviewSurfaceProps = {
    slug: string;
    surface: string;
    settings: Record<string, string | boolean | number>;
    components: TemplateManifest['components'];
};

/**
 * Renderiza en vivo el tema a partir de su código fuente (rutas de
 * `manifest.components`), sin depender del bundle `dist/{slug}.js` ni del
 * import map. Los loaders lazy viven en `@/lib/theme-loaders` (nivel de
 * módulo): `import.meta.glob` genera loaders por archivo de layouts/, así Vite
 * los compila en dev y los agrupa en chunks en el build.
 */
export default function PreviewSurface({
    slug,
    surface,
    settings,
    components,
}: PreviewSurfaceProps) {
    const settingsStr = JSON.stringify(settings);

    useEffect(() => {
        if (typeof window !== 'undefined') {
            window.__TPL_SETTINGS__ = settings;
            window.dispatchEvent(new Event('tpl:settings-changed'));
        }
    }, [settingsStr]);

    return (
        <div className="relative min-h-full">
            <ErrorBoundary>
                <Suspense fallback={<PreviewLoading />}>
                    <DynamicTemplate
                        key={surface}
                        slug={slug}
                        surface={surface}
                        components={components}
                    />
                </Suspense>
            </ErrorBoundary>
            <SurfaceBadge surface={surface} />
        </div>
    );
}

function DynamicTemplate({
    slug,
    surface,
    components,
}: Omit<PreviewSurfaceProps, 'settings'>) {
    const [cacheBust] = useState(() => (import.meta.env.DEV ? `?t=${Date.now()}` : ''));

    const cssUrl = useMemo(
        () => `${window.location.origin}/templates/${slug}/dist/${slug}.css${cacheBust}`,
        [slug, cacheBust],
    );

    const surfaceRoles = components[surface] ?? {};
    const templateRel = surfaceRoles.template;
    const headerRel = surfaceRoles.header;
    const footerRel = surfaceRoles.footer;
    const topmenuRel = surfaceRoles.topmenu;
    const sidebarRel = surfaceRoles.sidebar;
    const loginRel = surfaceRoles.login;

    const TemplateRef = themeComponent(templateRel);
    const HeaderRef = themeComponent(headerRel);
    const FooterRef = themeComponent(footerRel);
    const TopmenuRef = themeComponent(topmenuRel);
    const SidebarRef = themeComponent(sidebarRel);
    const LoginRef = themeComponent(loginRel);

    const isAuth = surface === 'auth';

    let main: React.ReactNode;

    if (TemplateRef) {
        main = (
            <TemplateRef>
                {isAuth && LoginRef ? <LoginRef /> : <SurfaceContent surface={surface} />}
            </TemplateRef>
        );
    } else if (isAuth && LoginRef) {
        main = <LoginRef />;
    } else {
        main = <PreviewEmpty surface={surface} />;
    }

    return (
        <>
            <link rel="stylesheet" href={cssUrl} />
            <div className="tpl-root min-h-screen bg-background text-content">
                {HeaderRef ? (
                    <ErrorBoundaryHOC surface={surface}>
                        <HeaderRef />
                    </ErrorBoundaryHOC>
                ) : null}
                {TopmenuRef ? (
                    <ErrorBoundaryHOC surface={surface}>
                        <TopmenuRef />
                    </ErrorBoundaryHOC>
                ) : null}
                <div className={SidebarRef ? 'flex' : undefined}>
                    {SidebarRef ? (
                        <ErrorBoundaryHOC surface={surface}>
                            <SidebarRef />
                        </ErrorBoundaryHOC>
                    ) : null}
                    <div className={SidebarRef ? 'flex-1' : undefined}>
                        <ErrorBoundaryHOC surface={surface}>{main}</ErrorBoundaryHOC>
                    </div>
                </div>
                {FooterRef ? (
                    <ErrorBoundaryHOC surface={surface}>
                        <FooterRef />
                    </ErrorBoundaryHOC>
                ) : null}
            </div>
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

function SurfaceBadge({ surface }: { surface: string }) {
    return (
        <span className="absolute left-3 top-3 z-50 rounded-full bg-background/90 px-3 py-1 text-xs font-medium capitalize text-foreground shadow ring-1 ring-border backdrop-blur">
            {surface}
        </span>
    );
}

function SurfaceContent({ surface }: { surface: string }) {
    return (
        <div className="rounded-xl border-2 border-dashed border-border p-8">
            <h2 className="mb-2 text-xl font-semibold capitalize text-foreground">
                Superficie {surface}
            </h2>
            <p className="text-sm text-muted-foreground">
                Contenido de ejemplo para la superficie "{surface}". Este bloque es un
                placeholder del preview; el template real se renderiza a través de su layout.
            </p>
        </div>
    );
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
                    No existe un template para la superficie "{surface}" en este tema.
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
