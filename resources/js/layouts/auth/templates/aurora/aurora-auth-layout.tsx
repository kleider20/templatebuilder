import type { ReactNode } from 'react';

interface AuroraLayoutProps {
    children: ReactNode;
}

export default function AuroraLayout({ children }: AuroraLayoutProps) {
    return (
        <div className="tpl-root min-h-screen bg-background text-content">
            <main className="mx-auto max-w-7xl px-6 py-12">{children}</main>
        </div>
    );
}