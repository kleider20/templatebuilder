import { usePage } from '@inertiajs/react';
import ThemeSurface from '@/components/theme-surface';
import LandingDefaultTemplate from '@/layouts/default/landing-default-template';
import type { ThemeApplications } from '@/types';

interface LandingLayoutProps {
    children: React.ReactNode;
}

export default function LandingLayout({ children }: LandingLayoutProps) {
    const { themeApplications } = usePage<{ themeApplications: ThemeApplications }>().props;

    return (
        <ThemeSurface
            surface="landing"
            fallback={<LandingDefaultTemplate>{children}</LandingDefaultTemplate>}
        >
            {children}
        </ThemeSurface>
    );
}