import FormsDefaultTemplate from '@/layouts/default/forms-default-template';

interface FormsLayoutProps {
    children: React.ReactNode;
}

export default function FormsLayout({ children }: FormsLayoutProps) {
    return <FormsDefaultTemplate>{children}</FormsDefaultTemplate>;
}