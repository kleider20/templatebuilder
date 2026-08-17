import LandingDefaultLayout from '@/layouts/default/components/landing/landing-default-layout';

interface LandingDefaultTemplateProps {
    breadcrumbs?: { label: string; href?: string }[];
    children: React.ReactNode;
}

export default function LandingDefaultTemplate({ children }: LandingDefaultTemplateProps) {
    return <LandingDefaultLayout>{children}</LandingDefaultLayout>;
}
