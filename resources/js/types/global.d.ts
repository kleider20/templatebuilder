import type { Auth } from '@/types/auth';
import type { ThemeApplications } from '@/types/templates';

declare module 'react' {
    // eslint-disable-next-line @typescript-eslint/no-unused-vars
    interface InputHTMLAttributes<T> {
        passwordrules?: string;
    }
}

declare module '@inertiajs/core' {
    export interface InertiaConfig {
        sharedPageProps: {
            name: string;
            auth: Auth;
            sidebarOpen: boolean;
            themeApplications: ThemeApplications;
            [key: string]: unknown;
        };
    }
}
