import FormsLayout from '@/layouts/forms/default/forms-layout';

interface FormsTemplateProps {
    breadcrumbs?: { label: string; href?: string }[];
    children: React.ReactNode;
}

export default function FormsTemplate({ children }: FormsTemplateProps) {
    return <FormsLayout>{children}</FormsLayout>;
}
