import { Head, router } from '@inertiajs/react';
import Editor from '@monaco-editor/react';
import { useCallback, useEffect, useMemo, useState } from 'react';
import ManifestEditor from '@/components/manifest-editor';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { update as updateEditor } from '@/routes/templates/editor';
import type { TemplateManifest, TemplateTreeFile } from '@/types';

type EditorPageProps = {
    slug: string;
    manifest: TemplateManifest;
    tree: TemplateTreeFile[];
    activeFile: { path: string; content: string } | null;
    editorExtensions: string[];
};

const languageFor = (path: string): string => {
    const ext = path.split('.').pop();

    if (ext === 'tsx' || ext === 'ts') {
        return 'typescript';
    }

    if (ext === 'json') {
        return 'json';
    }

    if (ext === 'css') {
        return 'css';
    }

    return 'plaintext';
};

type SectionFile = { name: string; label: string; path: string };
type SectionGroup = { section: string; files: SectionFile[] };

const groupTreeBySection = (tree: TemplateTreeFile[]): SectionGroup[] => {
    const bySection = new Map<string, TemplateTreeFile[]>();

    for (const file of tree) {
        const { section } = describeSection(file.path);
        const files = bySection.get(section) ?? [];

        files.push(file);
        bySection.set(section, files);
    }

    const groups: SectionGroup[] = [];

    for (const [section, files] of bySection) {
        const counts = new Map<string, number>();

        for (const file of files) {
            counts.set(file.name, (counts.get(file.name) ?? 0) + 1);
        }

        groups.push({
            section,
            files: files.map((file) => {
                const relative = describeSection(file.path).relative;
                const duplicate = counts.get(file.name)! > 1;

                return {
                    name: file.name,
                    label: duplicate ? relative : file.name,
                    path: file.path,
                };
            }),
        });
    }

    return groups;
};

/**
 * Deriva la sección (tipo de superficie) y la ruta relativa de un archivo del
 * tema. Los componentes viven en components/{tipo}/ y los templates en la raíz
 * del home ({tipo}-{slug}-template.tsx).
 */
const describeSection = (path: string): { section: string; relative: string } => {
    const componentMatch = path.match(/components\/(\w+)\/(.+)$/);

    if (componentMatch) {
        return { section: componentMatch[1], relative: componentMatch[2] };
    }

    const type = path.split('/').pop()?.split('-')[0] ?? 'otros';

    return { section: type, relative: path.split('/').pop() ?? path };
};

export default function TemplateEditor({
    slug,
    manifest,
    tree,
    activeFile,
}: EditorPageProps) {
    const path = activeFile?.path ?? '';

    const groups = useMemo(() => groupTreeBySection(tree), [tree]);

    const onSelect = useCallback((filePath: string) => {
        router.get(window.location.pathname, { path: filePath }, { preserveState: true });
    }, []);

    return (
        <>
            <Head title={`Editor - ${manifest.name}`} />

            <div className="flex h-[calc(100vh-4rem)] flex-col gap-4 p-4 sm:p-6">
                <div className="flex flex-wrap items-center justify-between gap-3">
                    <div className="flex items-center gap-3">
                        <h1 className="text-xl font-semibold text-foreground">
                            {manifest.name}
                        </h1>
                        <Badge variant="secondary">v{manifest.version}</Badge>
                        <span className="text-sm text-muted-foreground">
                            {slug}
                        </span>
                    </div>

                    <div className="flex items-center gap-2">
                        <Input
                            className="w-64"
                            value={path}
                            readOnly
                            aria-label="Archivo activo"
                        />
                        <ManifestEditor manifest={manifest} slug={slug} />
                    </div>
                </div>

                <div className="grid min-h-0 flex-1 grid-cols-[280px_1fr] gap-4">
                    <Card className="flex min-h-0 flex-col overflow-hidden">
                        <div className="border-b border-border p-3 text-sm font-medium">
                            Archivos
                        </div>
                        <div className="min-h-0 flex-1 overflow-auto p-2">
                            <div className="flex flex-col gap-4">
                                {groups.map(({ section, files }) => (
                                    <div key={section} className="flex flex-col gap-0.5">
                                        <div className="px-3 pb-1 pt-2 text-xs font-semibold uppercase tracking-wide text-muted-foreground">
                                            {section}
                                        </div>
                                        {files.map((file) => (
                                            <button
                                                key={file.path}
                                                type="button"
                                                onClick={() => onSelect(file.path)}
                                                className={`flex items-center justify-between gap-2 rounded-md px-3 py-1.5 text-left text-sm transition-colors ${
                                                    file.path === path
                                                        ? 'bg-primary text-primary-foreground'
                                                        : 'text-foreground hover:bg-muted'
                                                }`}
                                            >
                                                <span className="truncate">
                                                    {file.label}
                                                </span>
                                                <span className="text-xs opacity-60">
                                                    {file.name.split('.').pop()}
                                                </span>
                                            </button>
                                        ))}
                                    </div>
                                ))}
                            </div>
                        </div>
                    </Card>

                    <FileEditor
                        key={activeFile?.path}
                        slug={slug}
                        activeFile={activeFile}
                    />
                </div>
            </div>
        </>
    );
}

function FileEditor({
    slug,
    activeFile,
}: {
    slug: string;
    activeFile: { path: string; content: string } | null;
}) {
    const [content, setContent] = useState(activeFile?.content ?? '');
    const [dirty, setDirty] = useState(false);

    const save = useCallback(() => {
        router.put(updateEditor.url(slug), { path: activeFile?.path ?? '', content }, {
            preserveScroll: true,
            preserveState: true,
            onSuccess: () => setDirty(false),
        });
    }, [activeFile?.path, content, slug]);

    useEffect(() => {
        const onKeyDown = (event: KeyboardEvent) => {
            if ((event.ctrlKey || event.metaKey) && event.key.toLowerCase() === 's') {
                event.preventDefault();
                save();
            }
        };

        window.addEventListener('keydown', onKeyDown);

        return () => window.removeEventListener('keydown', onKeyDown);
    }, [save]);

    const language = useMemo(() => languageFor(activeFile?.path ?? ''), [activeFile?.path]);

    return (
        <Card className="flex min-h-0 flex-col overflow-hidden">
            <div className="flex items-center justify-between gap-2 border-b border-border p-3">
                <span className="truncate text-sm font-medium text-muted-foreground">
                    {activeFile?.path ?? 'Sin archivo seleccionado'}
                </span>
                <Button onClick={save} disabled={!dirty}>
                    {dirty ? 'Guardar cambios' : 'Guardado'}
                </Button>
            </div>
            <Editor
                height="100%"
                language={language}
                theme="vs-dark"
                value={content}
                onChange={(value) => {
                    setContent(value ?? '');
                    setDirty(true);
                }}
                options={{
                    minimap: { enabled: false },
                    fontSize: 13,
                    tabSize: 2,
                    automaticLayout: true,
                }}
            />
        </Card>
    );
}

TemplateEditor.layout = {
    breadcrumbs: [
        { title: 'Plantillas', href: '/templates' },
    ],
};
