import { Head } from '@inertiajs/react';
import { useState } from 'react';
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
import type { TemplateCatalogItem, TemplatePricing } from '@/types';

const surfaceLabels: Record<string, string> = {
    landing: 'Landing',
    dashboard: 'Dashboard',
    forms: 'Forms',
    auth: 'Auth',
};

type Filter = 'all' | 'free' | 'premium';

type MarketplaceItem = TemplateCatalogItem & {
    downloadable: boolean;
    download_url: string | null;
};

type MarketplacePageProps = {
    templates: MarketplaceItem[];
};

function PricingBadge({ pricing }: { pricing: TemplatePricing }) {
    if (pricing.type === 'free') {
        return (
            <Badge className="bg-emerald-500 text-white hover:bg-emerald-500">FREE</Badge>
        );
    }

    const price = pricing.price != null ? `$${Number(pricing.price).toFixed(2)}` : 'Premium';

    return <Badge variant="secondary">{price}</Badge>;
}

export default function Marketplace({ templates }: MarketplacePageProps) {
    const [filter, setFilter] = useState<Filter>('all');

    const filtered = templates.filter((template) => {
        const type = template.pricing?.type ?? 'free';

        return filter === 'all' || type === filter;
    });

    return (
        <>
            <Head title="Marketplace" />

            <div className="flex min-h-screen flex-col items-center gap-8 px-4 py-10">
                <div className="text-center">
                    <h1 className="text-3xl font-semibold text-foreground">
                        Marketplace de plantillas
                    </h1>
                    <p className="mt-2 text-sm text-muted-foreground">
                        Elige un tema gratis o premium, descárgalo e instálalo en tu proyecto.
                    </p>
                </div>

                <div className="flex flex-wrap items-center justify-center gap-2">
                    {([
                        ['all', 'Todos'],
                        ['free', 'Gratis'],
                        ['premium', 'Premium'],
                    ] as [Filter, string][]).map(([value, label]) => (
                        <Button
                            key={value}
                            size="sm"
                            variant={filter === value ? 'default' : 'outline'}
                            onClick={() => setFilter(value)}
                        >
                            {label}
                        </Button>
                    ))}
                </div>

                <div className="grid w-full max-w-6xl grid-cols-1 gap-6 md:grid-cols-2 xl:grid-cols-3">
                    {filtered.map((template) => (
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
                                <div className="absolute right-2 top-2">
                                    <PricingBadge pricing={template.pricing ?? { type: 'free', price: null, currency: 'USD' }} />
                                </div>
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
                                <Badge variant="outline">{template.author}</Badge>
                            </CardContent>

                            <CardFooter>
                                {template.downloadable && template.download_url ? (
                                    <a href={template.download_url} className="w-full">
                                        <Button className="w-full">Descargar</Button>
                                    </a>
                                ) : (
                                    <Button className="w-full" disabled>
                                        No disponible
                                    </Button>
                                )}
                            </CardFooter>
                        </Card>
                    ))}
                </div>

                {filtered.length === 0 && (
                    <p className="text-sm text-muted-foreground">
                        No hay plantillas para este filtro.
                    </p>
                )}
            </div>
        </>
    );
}