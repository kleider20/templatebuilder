import AuroraLayout from '@/layouts/landing/templates/aurora/aurora-layout';

interface AuroraTemplateProps {
    breadcrumbs?: { label: string; href?: string }[];
    children: React.ReactNode;
}

export default function AuroraTemplate({ children }: AuroraTemplateProps) {
    return <AuroraLayout>{children}</AuroraLayout>;
}