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

export default function TemplateEditor({
    slug,
    manifest,
    tree,
    activeFile,
    editorExtensions,
}: EditorPageProps) {
    const [path, setPath] = useState(activeFile?.path ?? '');
    const [content, setContent] = useState(activeFile?.content ?? '');
    const [dirty, setDirty] = useState(false);

    const onSelect = useCallback((filePath: string) => {
        setPath(filePath);
        setDirty(false);
        router.get(window.location.pathname, { path: filePath }, { preserveState: true });
    }, []);

    const save = useCallback(() => {
        router.put(updateEditor.url(slug), { path, content }, {
            preserveScroll: true,
            preserveState: true,
            onSuccess: () => setDirty(false),
        });
    }, [content, path, slug]);

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

    const language = useMemo(() => languageFor(path), [path]);

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
                        <Button onClick={save} disabled={!dirty}>
                            {dirty ? 'Guardar cambios' : 'Guardado'}
                        </Button>
                    </div>
                </div>

                <div className="grid min-h-0 flex-1 grid-cols-[280px_1fr] gap-4">
                    <Card className="flex min-h-0 flex-col overflow-hidden">
                        <div className="border-b border-border p-3 text-sm font-medium">
                            Archivos
                        </div>
                        <div className="min-h-0 flex-1 overflow-auto p-2">
                            <div className="flex flex-col gap-0.5">
                                {tree.map((file) => (
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
                                            {file.path}
                                        </span>
                                        <span className="text-xs opacity-60">
                                            {file.name.split('.').pop()}
                                        </span>
                                    </button>
                                ))}
                            </div>
                        </div>
                    </Card>

                    <Card className="min-h-0 overflow-hidden">
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
                </div>
            </div>
        </>
    );
}

TemplateEditor.layout = {
    breadcrumbs: [
        { title: 'Plantillas', href: '/templates' },
    ],
};
