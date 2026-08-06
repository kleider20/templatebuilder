import DashboardLayout from '@/layouts/dashboard/default/dashboard-layout';

interface DashboardTemplateProps {
    breadcrumbs?: { label: string; href?: string }[];
    children: React.ReactNode;
}

export default function DashboardTemplate({ children }: DashboardTemplateProps) {
    return <DashboardLayout>{children}</DashboardLayout>;
}
