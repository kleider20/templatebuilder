import type { ReactNode } from 'react';

interface AuthDefaultLayoutProps {
    children: ReactNode;
}

export default function AuthDefaultLayout({ children }: AuthDefaultLayoutProps) {
    return (
        <div className="tpl-root min-h-screen bg-background text-content">
            <main className="mx-auto flex min-h-screen max-w-md flex-col justify-center px-6 py-12">
                <span className="mb-6 text-lg font-semibold text-primary">Auth</span>
                {children}
            </main>
        </div>
    );
}
