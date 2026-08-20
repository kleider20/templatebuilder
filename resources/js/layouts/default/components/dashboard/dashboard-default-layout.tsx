import type { ReactNode } from 'react';
import { useState } from 'react';
import DashboardDefaultSidebar from './dashboard-default-sidebar';
import DashboardDefaultTopbar from './dashboard-default-topbar';

interface DashboardDefaultLayoutProps {
    children: ReactNode;
}

export default function DashboardDefaultLayout({ children }: DashboardDefaultLayoutProps) {
    const [mobileOpen, setMobileOpen] = useState(false);

    return (
        <div className="tpl-root min-h-screen bg-background text-foreground">
            <div className="flex min-h-screen">
                <DashboardDefaultSidebar
                    mobileOpen={mobileOpen}
                    onClose={() => setMobileOpen(false)}
                />
                <div className="flex min-w-0 flex-1 flex-col">
                    <DashboardDefaultTopbar onMenuClick={() => setMobileOpen((open) => !open)} />
                    <main className="flex-1 p-6">{children}</main>
                </div>
            </div>
        </div>
    );
}
