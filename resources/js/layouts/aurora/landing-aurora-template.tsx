import AuroraLayout from '@/layouts/aurora/components/landing/landing-aurora-layout';

interface AuroraTemplateProps {
    breadcrumbs?: { label: string; href?: string }[];
    children: React.ReactNode;
}

export default function AuroraTemplate({ children }: AuroraTemplateProps) {
    return <AuroraLayout>{children}</AuroraLayout>;
}