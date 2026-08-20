import { Link, router, usePage } from '@inertiajs/react';
import {
    ChevronDown,
    ChevronUp,
    FileCode2,
    KeyRound,
    Layers,
    LayoutDashboard,
    LogOut,
    Settings2,
    User,
} from 'lucide-react';
import type { ReactNode } from 'react';
import { useEffect, useRef, useState } from 'react';
import { useInitials } from '@/hooks/use-initials';
import { cn } from '@/lib/utils';
import { dashboard, logout } from '@/routes';
import { plantillas } from '@/routes/dashboard';
import { edit as profile } from '@/routes/profile';
import { edit as security } from '@/routes/security';

interface NavItemProps {
    href: string;
    active: boolean;
    icon: ReactNode;
    children: ReactNode;
}

function NavItem({ href, active, icon, children }: NavItemProps) {
    return (
        <Link
            href={href}
            className={cn(
                'flex items-center gap-3 rounded-lg px-3 py-2 text-sm transition-colors',
                active
                    ? 'bg-primary/10 text-primary'
                    : 'text-muted-foreground hover:bg-muted hover:text-foreground',
            )}
        >
            {icon}
            <span className="flex-1">{children}</span>
        </Link>
    );
}

interface NavGroupProps {
    label: string;
    children: ReactNode;
}

function NavGroup({ label, children }: NavGroupProps) {
    return (
        <div>
            <div className="mb-1 px-3 text-[10px] font-semibold uppercase tracking-wider text-muted-foreground">
                {label}
            </div>
            <div className="space-y-0.5">{children}</div>
        </div>
    );
}

interface DashboardDefaultSidebarProps {
    mobileOpen: boolean;
    onClose: () => void;
}

export default function DashboardDefaultSidebar({ mobileOpen, onClose }: DashboardDefaultSidebarProps) {
    const { url, props } = usePage();
    const { auth } = props as { auth?: { user?: { name: string; email: string } } };
    const getInitials = useInitials();
    const [dropdownOpen, setDropdownOpen] = useState(false);
    const dropdownRef = useRef<HTMLDivElement>(null);

    useEffect(() => {
        const onClickOutside = (event: MouseEvent) => {
            if (!dropdownRef.current?.contains(event.target as Node)) {
                setDropdownOpen(false);
            }
        };

        document.addEventListener('mousedown', onClickOutside);

        return () => document.removeEventListener('mousedown', onClickOutside);
    }, []);

    const user = auth?.user;
    const userName = user?.name ?? '';
    const initials = getInitials(userName);

    const nav = [
        {
            label: 'Principal',
            items: [
                {
                    label: 'Dashboard',
                    href: dashboard().url,
                    active: url === '/dashboard' || url.startsWith('/dashboard/'),
                    icon: <LayoutDashboard className="size-4" />,
                },
                {
                    label: 'Plantillas',
                    href: plantillas().url,
                    active: url.startsWith('/dashboard/plantillas'),
                    icon: <FileCode2 className="size-4" />,
                },
            ],
        },
        {
            label: 'Sistema',
            items: [
                {
                    label: 'Passkeys',
                    href: security().url,
                    active: url.startsWith('/settings/security'),
                    icon: <KeyRound className="size-4" />,
                },
                {
                    label: 'Configuración',
                    href: profile().url,
                    active: url.startsWith('/settings/profile'),
                    icon: <Settings2 className="size-4" />,
                },
            ],
        },
    ];

    return (
        <>
            {mobileOpen ? (
                <div
                    className="fixed inset-0 z-40 bg-black/60 backdrop-blur-sm lg:hidden"
                    onClick={onClose}
                    aria-hidden="true"
                />
            ) : null}
            <aside
                className={cn(
                    'fixed inset-y-0 left-0 z-50 flex w-64 flex-col border-r border-border bg-card transition-transform lg:sticky lg:top-0 lg:z-auto lg:h-screen lg:translate-x-0 lg:bg-card',
                    mobileOpen ? 'translate-x-0' : '-translate-x-full',
                )}
            >
                <div className="flex h-16 items-center gap-2.5 border-b border-border px-5">
                    <div className="flex size-8 items-center justify-center rounded-lg bg-primary text-primary-foreground">
                        <Layers className="size-4" />
                    </div>
                    <div>
                        <div className="text-sm font-semibold tracking-tight text-foreground">
                            TemplateForge
                        </div>
                        <div className="-mt-0.5 text-[10px] text-muted-foreground">
                            v1.0 · Laravel 13
                        </div>
                    </div>
                </div>

                <nav className="flex-1 space-y-5 overflow-y-auto px-3 py-4">
                    {nav.map((group) => (
                        <NavGroup key={group.label} label={group.label}>
                            {group.items.map((item) => (
                                <NavItem key={item.label} {...item}>
                                    {item.label}
                                </NavItem>
                            ))}
                        </NavGroup>
                    ))}
                </nav>

                <div className="border-t border-border p-3">
                    <div className="relative" ref={dropdownRef}>
                        <button
                            type="button"
                            className="flex w-full items-center gap-3 rounded-lg p-2 text-left transition-colors hover:bg-muted"
                            onClick={() => setDropdownOpen((open) => !open)}
                        >
                            <span className="flex size-9 flex-shrink-0 items-center justify-center rounded-full bg-muted text-sm font-medium text-foreground ring-1 ring-border">
                                {initials}
                            </span>
                            <span className="min-w-0 flex-1">
                                <span className="block truncate text-sm font-medium text-foreground">
                                    {userName}
                                </span>
                                <span className="block truncate text-[11px] text-muted-foreground">
                                    {user?.email}
                                </span>
                            </span>
                            {dropdownOpen ? (
                                <ChevronUp className="size-4 text-muted-foreground" />
                            ) : (
                                <ChevronDown className="size-4 text-muted-foreground" />
                            )}
                        </button>

                        {dropdownOpen ? (
                            <div className="absolute bottom-full left-0 right-0 z-50 mb-2 rounded-xl border border-border bg-card p-1.5 shadow-lg">
                                <div className="border-b border-border px-3 py-2">
                                    <div className="text-sm font-medium text-foreground">{userName}</div>
                                    <div className="text-xs text-muted-foreground">
                                        {user?.email}
                                    </div>
                                </div>
                                <Link
                                    href={profile().url}
                                    className="flex items-center gap-2.5 rounded-lg px-3 py-2 text-sm text-foreground transition-colors hover:bg-muted"
                                    onClick={() => setDropdownOpen(false)}
                                >
                                    <User className="size-4 text-muted-foreground" />
                                    Mi Perfil
                                </Link>
                                <Link
                                    href={security().url}
                                    className="flex items-center gap-2.5 rounded-lg px-3 py-2 text-sm text-foreground transition-colors hover:bg-muted"
                                    onClick={() => setDropdownOpen(false)}
                                >
                                    <KeyRound className="size-4 text-muted-foreground" />
                                    Passkeys
                                </Link>
                                <div className="my-1 border-t border-border" />
                                <button
                                    type="button"
                                    onClick={() => router.post(logout.url())}
                                    className="flex w-full items-center gap-2.5 rounded-lg px-3 py-2 text-sm text-destructive transition-colors hover:bg-destructive/10"
                                >
                                    <LogOut className="size-4" />
                                    Cerrar sesión
                                </button>
                            </div>
                        ) : null}
                    </div>
                </div>
            </aside>
        </>
    );
}
