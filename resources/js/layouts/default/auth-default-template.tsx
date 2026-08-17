import AuthDefaultLayout from '@/layouts/default/components/auth/auth-default-layout';

interface AuthDefaultTemplateProps {
    breadcrumbs?: { label: string; href?: string }[];
    children: React.ReactNode;
}

export default function AuthDefaultTemplate({ children }: AuthDefaultTemplateProps) {
    return <AuthDefaultLayout>{children}</AuthDefaultLayout>;
}