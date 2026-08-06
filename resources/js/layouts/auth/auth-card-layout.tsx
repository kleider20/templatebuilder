import type { ReactNode } from 'react';
import AppearanceToggleTab from '@/components/appearance-tabs';
import AppLogo from '@/components/app-logo';
import { Card, CardContent, CardHeader } from '@/components/ui/card';

export default function AuthCardLayout({
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
            <div className="min-h-svh flex flex-col items-center justify-center gap-6 p-6 md:p-10">
                <AppLogo />
                <Card className="w-full max-w-sm">
                    <CardHeader className="space-y-1.5">
                        <h1 className="text-xl font-semibold tracking-tight">
                            {title}
                        </h1>
                        {description && (
                            <p className="text-sm text-muted-foreground">
                                {description}
                            </p>
                        )}
                    </CardHeader>
                    <CardContent className="border-border pt-0">
                        {children}
                    </CardContent>
                </Card>
                <AppearanceToggleTab className="fixed top-2 right-2" />
            </div>
        </div>
    );
}