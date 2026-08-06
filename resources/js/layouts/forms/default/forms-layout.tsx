import type { ReactNode } from 'react';

interface FormsLayoutProps {
    children: ReactNode;
}

export default function FormsLayout({ children }: FormsLayoutProps) {
    return (
        <div className="tpl-root min-h-screen bg-background text-content">
            <header className="border-b border-border bg-background/80 backdrop-blur">
                <div className="mx-auto flex max-w-7xl items-center justify-between px-6 py-4">
                    <span className="text-lg font-semibold text-primary">Forms</span>
                </div>
            </header>
            <main className="mx-auto max-w-7xl px-6 py-12">{children}</main>
            <footer className="border-t border-border bg-surface py-6 text-content">
                <div className="mx-auto max-w-7xl px-6 text-sm text-muted-foreground">
                    Tema default forms.
                </div>
            </footer>
        </div>
    );
}
