import { Link, usePage } from '@inertiajs/react';
import type { ReactNode } from 'react';
import { dashboard, login, register } from '@/routes';

interface LandingDefaultLayoutProps {
    children: ReactNode;
}

export default function LandingDefaultLayout({ children }: LandingDefaultLayoutProps) {
    const { auth } = usePage().props;

    return (
        <div className="tpl-root min-h-screen bg-background text-content">
            <header className="border-b border-border bg-background/80 backdrop-blur">
                <div className="mx-auto flex max-w-7xl items-center justify-between px-6 py-4">
                    <span className="text-lg font-semibold text-primary">Landing</span>
                    <nav className="flex items-center gap-4 text-sm">
                        {auth.user ? (
                            <Link
                                href={dashboard()}
                                className="rounded-sm bg-primary px-4 py-1.5 text-background hover:opacity-90"
                            >
                                Dashboard
                            </Link>
                        ) : (
                            <>
                                <Link href={login()} className="text-content hover:text-primary">
                                    Log in
                                </Link>
                                <Link
                                    href={register()}
                                    className="rounded-sm bg-primary px-4 py-1.5 text-background hover:opacity-90"
                                >
                                    Register
                                </Link>
                            </>
                        )}
                    </nav>
                </div>
            </header>
            <main className="mx-auto max-w-7xl px-6 py-12">{children}</main>
            <footer className="border-t border-border bg-surface py-6 text-content">
                <div className="mx-auto max-w-7xl px-6 text-sm text-muted-foreground">
                    Tema default landing.
                </div>
            </footer>
        </div>
    );
}
