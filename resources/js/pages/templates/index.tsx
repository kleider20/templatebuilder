import { Form, Head, Link, router } from '@inertiajs/react';
import { useRef } from 'react';
import { toast } from 'sonner';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardFooter,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { PlaceholderPattern } from '@/components/ui/placeholder-pattern';
import { create, editor, exportMethod } from '@/routes/templates';
import { show } from '@/routes/templates/preview';
import { update as updateThumbnail } from '@/routes/templates/thumbnail';
import type { TemplateCatalogItem } from '@/types';

const surfaceLabels: Record<string, string> = {
    landing: 'Landing',
    dashboard: 'Dashboard',
    forms: 'Forms',
    auth: 'Auth',
};

type TemplatesPageProps = {
    templates: TemplateCatalogItem[];
    surfaces: string[];
};

function ThumbnailUpload({ slug }: { slug: string }) {
    const inputRef = useRef<HTMLInputElement>(null);

    const onUpload = (event: React.ChangeEvent<HTMLInputElement>) => {
        const file = event.target.files?.[0];

        if (!file) {
            return;
        }

        const data = new FormData();
        data.append('file', file);

        router.post(updateThumbnail.url(slug), data, {
            preserveScroll: true,
            preserveState: true,
            forceFormData: true,
            onSuccess: () => {
                toast.success('Preview actualizada.');
                router.reload({ only: ['templates'] });
            },
            onError: () => {
                toast.error('No se pudo subir la preview.');
            },
        });

        event.target.value = '';
    };

    return (
        <>
            <input
                ref={inputRef}
                type="file"
                accept="image/png,image/jpeg,image/webp,image/gif"
                className="hidden"
                onChange={onUpload}
            />
            <Button variant="outline" size="sm" onClick={() => inputRef.current?.click()}>
                Subir preview
            </Button>
        </>
    );
}

export default function Templates({ templates, surfaces }: TemplatesPageProps) {
    return (
        <>
            <Head title="Plantillas" />

            <div className="flex flex-col gap-6 p-4 sm:p-6">
                <div className="flex flex-wrap items-center justify-between gap-4">
                    <div>
                        <h1 className="text-2xl font-semibold text-foreground">
                            Plantillas
                        </h1>
                        <p className="mt-1 text-sm text-muted-foreground">
                            Temas multi-superficie disponibles en el builder.
                        </p>
                    </div>

                    <Link href={create()}>
                        <Button>Crear plantilla</Button>
                    </Link>
                </div>

                <div className="grid grid-cols-1 gap-6 md:grid-cols-2 xl:grid-cols-3">
                    {templates.map((template) => (
                        <Card key={template.slug} className="overflow-hidden">
                            <div className="relative aspect-video overflow-hidden border-b border-border bg-muted">
                                {template.thumbnail ? (
                                    <img
                                        src={template.thumbnail}
                                        alt={template.name}
                                        className="size-full object-cover"
                                    />
                                ) : (
                                    <PlaceholderPattern className="absolute inset-0 size-full stroke-neutral-900/20 dark:stroke-neutral-100/20" />
                                )}
                            </div>

                            <CardHeader>
                                <CardTitle className="flex items-center justify-between gap-2">
                                    <span>{template.name}</span>
                                    <span className="text-sm font-normal text-muted-foreground">
                                        v{template.version}
                                    </span>
                                </CardTitle>
                                <CardDescription>
                                    {template.description}
                                </CardDescription>
                            </CardHeader>

                            <CardContent className="flex flex-wrap gap-2">
                                {template.types.map((type) => (
                                    <Badge key={type} variant="secondary">
                                        {surfaceLabels[type] ?? type}
                                    </Badge>
                                ))}
                            </CardContent>

                            <CardFooter className="flex-wrap gap-2">
                                <Link href={editor(template.slug)}>
                                    <Button variant="outline" size="sm">
                                        Editor
                                    </Button>
                                </Link>
                                <Link href={show(template.slug)}>
                                    <Button variant="outline" size="sm">
                                        Preview
                                    </Button>
                                </Link>
                                <Form
                                    action={exportMethod.url(template.slug)}
                                    method="post"
                                    className="inline"
                                >
                                    <Button type="submit" size="sm">
                                        Exportar
                                    </Button>
                                </Form>
                                <ThumbnailUpload slug={template.slug} />
                            </CardFooter>
                        </Card>
                    ))}
                </div>
            </div>
        </>
    );
}

Templates.layout = {
    breadcrumbs: [
        {
            title: 'Plantillas',
            href: '/templates',
        },
    ],
};
