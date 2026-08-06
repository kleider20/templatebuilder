import WelcomeLayout from '@/layouts/landing/default/welcome-layout';

interface WelcomeTemplateProps {
    breadcrumbs?: { label: string; href?: string }[];
    children: React.ReactNode;
}

export default function WelcomeTemplate({ children }: WelcomeTemplateProps) {
    return <WelcomeLayout>{children}</WelcomeLayout>;
}
