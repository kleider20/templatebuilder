import type { ReactNode } from 'react';

export default function AuroraSidebar({ children }: { children?: ReactNode }) {
    return (
        <aside className="flex w-64 flex-col gap-2 border-r border-border bg-surface p-4">
            <span className="px-2 text-sm font-semibold text-primary">Aurora</span>
            {children}
        </aside>
    );
}