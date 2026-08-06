import type { ReactNode } from 'react';

interface DashboardLayoutProps {
    children: ReactNode;
}

export default function DashboardLayout({ children }: DashboardLayoutProps) {
    return (
        <div className="tpl-root min-h-screen bg-background text-content">
            <div className="flex">
                <aside className="flex w-64 flex-col gap-2 border-r border-border bg-surface p-4">
                    <span className="px-2 text-sm font-semibold text-primary">Dashboard</span>
                    <a href="#" className="px-2 text-sm text-content transition-colors hover:text-primary">
                        Inicio
                    </a>
                    <a href="#" className="px-2 text-sm text-content transition-colors hover:text-primary">
                        Configuración
                    </a>
                </aside>
                <main className="flex-1 p-6">{children}</main>
            </div>
        </div>
    );
}
