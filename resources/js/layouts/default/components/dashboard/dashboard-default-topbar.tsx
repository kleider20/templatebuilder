import { router } from '@inertiajs/react';
import { Bell, HelpCircle, Menu, Moon, Search, Sun } from 'lucide-react';
import { useEffect, useRef } from 'react';
import { useAppearance } from '@/hooks/use-appearance';
import { plantillas } from '@/routes/dashboard';

interface DashboardDefaultTopbarProps {
    onMenuClick: () => void;
}

export default function DashboardDefaultTopbar({ onMenuClick }: DashboardDefaultTopbarProps) {
    const { resolvedAppearance, updateAppearance } = useAppearance();
    const inputRef = useRef<HTMLInputElement>(null);

    useEffect(() => {
        const onKeyDown = (event: KeyboardEvent) => {
            if ((event.metaKey || event.ctrlKey) && event.key.toLowerCase() === 'k') {
                event.preventDefault();
                inputRef.current?.focus();
            }
        };

        document.addEventListener('keydown', onKeyDown);

        return () => document.removeEventListener('keydown', onKeyDown);
    }, []);

    const onSearchSubmit = (event: React.FormEvent<HTMLFormElement>) => {
        event.preventDefault();
        router.visit(plantillas());
    };

    const isDark = resolvedAppearance === 'dark';

    return (
        <header className="sticky top-0 z-30 flex h-16 items-center justify-between border-b border-border bg-background/80 px-6 backdrop-blur-xl">
            <div className="flex items-center gap-4">
                <button
                    type="button"
                    className="text-muted-foreground transition-colors hover:text-foreground lg:hidden"
                    onClick={onMenuClick}
                    aria-label="Abrir menú"
                >
                    <Menu className="size-5" />
                </button>

                <form onSubmit={onSearchSubmit} className="relative">
                    <Search className="absolute left-3 top-1/2 size-4 -translate-y-1/2 text-muted-foreground" />
                    <input
                        ref={inputRef}
                        type="text"
                        placeholder="Buscar plantillas..."
                        className="w-80 rounded-lg border border-border bg-muted py-2 pl-9 pr-4 text-sm text-foreground placeholder:text-muted-foreground focus:border-primary focus:bg-card focus:outline-none"
                    />
                    <kbd className="absolute right-3 top-1/2 -translate-y-1/2 rounded border border-border bg-card px-1.5 py-0.5 text-[10px] text-muted-foreground">
                        K
                    </kbd>
                </form>
            </div>

            <div className="flex items-center gap-2">
                <button
                    type="button"
                    className="rounded-lg p-2 text-muted-foreground transition-colors hover:bg-muted hover:text-foreground"
                    onClick={() => updateAppearance(isDark ? 'light' : 'dark')}
                    title={isDark ? 'Cambiar a modo claro' : 'Cambiar a modo oscuro'}
                >
                    {isDark ? <Sun className="size-4" /> : <Moon className="size-4" />}
                </button>

                <button
                    type="button"
                    className="relative rounded-lg p-2 text-muted-foreground transition-colors hover:bg-muted hover:text-foreground"
                    aria-label="Notificaciones"
                >
                    <Bell className="size-4" />
                    <span className="absolute right-1.5 top-1.5 size-2 rounded-full bg-primary ring-2 ring-background" />
                </button>

                <button
                    type="button"
                    className="rounded-lg p-2 text-muted-foreground transition-colors hover:bg-muted hover:text-foreground"
                    aria-label="Ayuda"
                >
                    <HelpCircle className="size-4" />
                </button>

                <div className="mx-1 h-6 w-px bg-border" />

                <div className="flex items-center gap-2 rounded-lg border border-emerald-500/20 bg-emerald-500/10 px-3 py-1.5">
                    <span className="size-1.5 animate-pulse rounded-full bg-emerald-400" />
                    <span className="text-xs font-medium text-emerald-400">Production</span>
                </div>
            </div>
        </header>
    );
}
