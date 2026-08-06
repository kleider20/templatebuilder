import type { ReactNode } from 'react';
import AppearanceToggleTab from '@/components/appearance-tabs';
import AppLogo from '@/components/app-logo';
import { cn } from '@/lib/utils';

export default function AuthSimpleLayout({
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
            <div
                className={cn(
                    'min-h-svh flex flex-col items-center justify-center gap-6 p-6 md:p-10',
                )}
            >
                <AppLogo />
                <div className="w-full max-w-sm space-y-6">
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
                <AppearanceToggleTab className="fixed top-2 right-2" />
            </div>
        </div>
    );
}