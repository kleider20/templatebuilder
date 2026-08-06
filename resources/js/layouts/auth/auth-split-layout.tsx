import type { ReactNode } from 'react';
import AppearanceToggleTab from '@/components/appearance-tabs';
import AppLogo from '@/components/app-logo';

export default function AuthSplitLayout({
    title = '',
    description = '',
    children,
}: {
    title?: string;
    description?: string;
    children: ReactNode;
}) {
    return (
        <div className="bg-background text-foreground">
            <div className="grid min-h-svh lg:grid-cols-2">
                <div className="hidden flex-col justify-between bg-muted p-10 lg:flex">
                    <AppLogo />
                    <p className="max-w-xs text-sm text-muted-foreground">
                        Plataforma de plantillas para construir productos
                        blandos, escalables y consistentes.
                    </p>
                </div>
                <div className="flex flex-col items-center justify-center p-6 md:p-10">
                    <div className="w-full max-w-sm space-y-6">
                        <AppLogo />
                        <header className="space-y-1.5">
                            <h1 className="text-xl font-semibold tracking-tight">
                                {title}
                            </h1>
                            {description && (
                                <p className="text-sm text-muted-foreground">
                                    {description}
                                </p>
                            )}
                        </header>
                        {children}
                    </div>
                </div>
                <AppearanceToggleTab className="fixed top-2 right-2" />
            </div>
        </div>
    );
}