import AuroraLayout from '@/layouts/forms/templates/aurora/aurora-forms-layout';

interface AuroraTemplateProps {
    breadcrumbs?: { label: string; href?: string }[];
    children: React.ReactNode;
}

export default function AuroraTemplate({ children }: AuroraTemplateProps) {
    return <AuroraLayout>{children}</AuroraLayout>;
}