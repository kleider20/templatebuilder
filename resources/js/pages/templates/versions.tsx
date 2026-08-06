import { Head, Link } from '@inertiajs/react';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import type { TemplateVersionRecord } from '@/types';

type VersionsProps = {
    slug?: string | null;
    versions: TemplateVersionRecord[];
};

export default function Versions({ slug, versions }: VersionsProps) {
    return (
        <>
            <Head title="Versiones" />

            <div className="flex flex-col gap-6 p-4 sm:p-6">
                <div>
                    <h1 className="text-2xl font-semibold text-foreground">
                        Versiones
                    </h1>
                    <p className="mt-1 text-sm text-muted-foreground">
                        {slug
                            ? `Historial de exportaciones de "${slug}".`
                            : 'Historial de exportaciones.'}
                    </p>
                </div>

                {versions.length === 0 ? (
                    <Card>
                        <CardContent className="py-10 text-center text-sm text-muted-foreground">
                            Sin versiones exportadas todavía.
                        </CardContent>
                    </Card>
                ) : (
                    <div className="flex flex-col gap-4">
                        {versions.map((version) => (
                            <Card key={version.id}>
                                <CardHeader>
                                    <CardTitle className="flex items-center justify-between">
                                        <span>
                                            {version.manifest.name ?? version.slug}
                                        </span>
                                        <Badge>v{version.version}</Badge>
                                    </CardTitle>
                                    <CardDescription>
                                        {new Date(
                                            version.created_at,
                                        ).toLocaleString()}
                                    </CardDescription>
                                </CardHeader>
                                <CardContent className="flex items-center justify-between gap-4">
                                    <span className="font-mono text-xs text-muted-foreground">
                                        sha256 {version.checksum.slice(0, 12)}…
                                    </span>
                                    <Link href={version.download_url}>
                                        <Button size="sm">Descargar ZIP</Button>
                                    </Link>
                                </CardContent>
                            </Card>
                        ))}
                    </div>
                )}
            </div>
        </>
    );
}

Versions.layout = {
    breadcrumbs: [
        { title: 'Plantillas', href: '/templates' },
        { title: 'Versiones' },
    ],
};
