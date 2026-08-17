import DashboardDefaultLayout from '@/layouts/default/components/dashboard/dashboard-default-layout';

interface DashboardDefaultTemplateProps {
    breadcrumbs?: { label: string; href?: string }[];
    children: React.ReactNode;
}

export default function DashboardDefaultTemplate({ children }: DashboardDefaultTemplateProps) {
    return <DashboardDefaultLayout>{children}</DashboardDefaultLayout>;
}
