import DashboardDefaultTemplate from '@/layouts/default/dashboard-default-template';

interface DashboardLayoutProps {
    children: React.ReactNode;
}

export default function DashboardLayout({ children }: DashboardLayoutProps) {
    return <DashboardDefaultTemplate>{children}</DashboardDefaultTemplate>;
}
