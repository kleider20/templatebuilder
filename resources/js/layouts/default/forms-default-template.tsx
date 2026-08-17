import FormsDefaultLayout from '@/layouts/default/components/forms/forms-default-layout';

interface FormsDefaultTemplateProps {
    breadcrumbs?: { label: string; href?: string }[];
    children: React.ReactNode;
}

export default function FormsDefaultTemplate({ children }: FormsDefaultTemplateProps) {
    return <FormsDefaultLayout>{children}</FormsDefaultLayout>;
}
