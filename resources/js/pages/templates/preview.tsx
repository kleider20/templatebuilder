import { Head } from '@inertiajs/react';
import { useCallback, useEffect, useMemo, useState } from 'react';
import PreviewSurface from '@/components/template-preview-surface';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import type {
    TemplateManifest,
    TemplatePalette,
    TemplateSettingSchema,
} from '@/types';

type PreviewProps = {
    slug: string;
    manifest: TemplateManifest;
    surfaces: string[];
};

const STORAGE_KEY = 'template-preview-state';
const fallbackZinc = { primary: '#18181b', secondary: '#52525b', accent: '#3f3f46' };

type Selection = {
    palette: string;
    overrides: Record<string, string>;
    settings: Record<string, string | boolean | number>;
};

function defaultSelection(manifest: TemplateManifest): Selection {
    const palette = manifest.theme?.default ?? 'zinc';

    return { palette, overrides: {}, settings: {} };
}

function paletteById(manifest: TemplateManifest, id: string): TemplatePalette | null {
    return manifest.theme?.palettes.find((p) => p.id === id) ?? null;
}

function resolveColor(
    manifest: TemplateManifest,
    selection: Selection,
    role: string,
    isDark: boolean,
): string {
    const palette = paletteById(manifest, selection.palette);

    if (selection.overrides[role]) {
        return selection.overrides[role];
    }

    const source = isDark ? palette?.dark ?? {} : palette?.colors ?? {};

    return source[role] ?? fallbackZinc[role as keyof typeof fallbackZinc] ?? '#000000';
}

function settingsDefaults(manifest: TemplateManifest): Record<string, string | boolean | number> {
    const result: Record<string, string | boolean | number> = {};

    for (const [key, schema] of Object.entries(manifest.settings ?? {})) {
        result[key] = schema.default;
    }

    return result;
}

export default function Preview({ slug, manifest, surfaces }: PreviewProps) {
    const [surface, setSurface] = useState(surfaces[0] ?? 'landing');
    const [isDark, setIsDark] = useState(false);
    const [selection, setSelection] = useState<Selection>(() => {
        const stored = localStorage.getItem(STORAGE_KEY);

        if (stored) {
            try {
                return { ...defaultSelection(manifest), ...JSON.parse(stored) };
            } catch {
                // ignorar
            }
        }

        return { ...defaultSelection(manifest), settings: settingsDefaults(manifest) };
    });

    useEffect(() => {
        document.documentElement.classList.toggle('dark', isDark);

        return () => document.documentElement.classList.remove('dark');
    }, [isDark]);

    useEffect(() => {
        localStorage.setItem(STORAGE_KEY, JSON.stringify(selection));
    }, [selection]);

    const roles = useMemo(
        () => manifest.theme?.roles ?? ['primary', 'secondary', 'accent', 'background', 'surface', 'text'],
        [manifest],
    );
    const editableRoles = (manifest.theme?.roles ?? []).filter((r) =>
        ['primary', 'secondary', 'accent'].includes(r),
    );

    const settingsSchema = manifest.settings ?? {};

    const setOverride = useCallback((role: string, value: string) => {
        setSelection((prev) => ({
            ...prev,
            overrides: { ...prev.overrides, [role]: value },
        }));
    }, []);

    const setSetting = useCallback((key: string, value: string | boolean | number) => {
        setSelection((prev) => ({
            ...prev,
            settings: { ...prev.settings, [key]: value },
        }));
    }, []);

    const reset = useCallback(() => {
        setSelection({ ...defaultSelection(manifest), settings: settingsDefaults(manifest) });
    }, [manifest]);

    const previewStyles = useMemo(() => {
        const colors = Object.fromEntries(
            roles.map((role) => [role, resolveColor(manifest, selection, role, isDark)]),
        );

        return Object.fromEntries(Object.entries(colors).map(([k, v]) => [`--tpl-${k}`, v]));
    }, [manifest, selection, isDark, roles]);

    const settingsEntry = Object.entries(settingsSchema) as [string, TemplateSettingSchema][];

    return (
        <>
            <Head title={`Preview - ${manifest.name}`} />

            <div className="flex h-[calc(100vh-4rem)] gap-0 overflow-hidden">
                <aside className="flex w-80 flex-col gap-6 overflow-y-auto border-r border-border p-4">
                    <div>
                        <h2 className="text-lg font-semibold text-foreground">
                            {manifest.name}
                        </h2>
                        <p className="text-sm text-muted-foreground">{slug}</p>
                    </div>

                    <div className="flex flex-col gap-2">
                        <Label>Superficie</Label>
                        <div className="flex flex-wrap gap-2">
                            {surfaces.map((s) => (
                                <Button
                                    key={s}
                                    size="sm"
                                    variant={surface === s ? 'default' : 'outline'}
                                    onClick={() => setSurface(s)}
                                >
                                    {s}
                                </Button>
                            ))}
                        </div>
                    </div>

                    <div className="flex flex-col gap-2">
                        <Label>Apariencia</Label>
                        <Button
                            variant="outline"
                            size="sm"
                            onClick={() => setIsDark((v) => !v)}
                        >
                            {isDark ? 'Modo claro' : 'Modo oscuro'}
                        </Button>
                    </div>

                    {manifest.theme && (
                        <div className="flex flex-col gap-2">
                            <div className="flex items-center justify-between">
                                <Label>Tema</Label>
                                <Button variant="ghost" size="sm" onClick={reset}>
                                    Restablecer
                                </Button>
                            </div>

                            <div className="grid grid-cols-2 gap-2">
                                {manifest.theme.palettes.map((p) => (
                                    <button
                                        key={p.id}
                                        type="button"
                                        onClick={() =>
                                            setSelection((prev) => ({
                                                ...prev,
                                                palette: p.id,
                                                overrides: {},
                                            }))
                                        }
                                        className={`flex flex-col gap-2 rounded-lg border p-3 text-left transition-colors ${
                                            selection.palette === p.id
                                                ? 'border-ring ring-1 ring-ring'
                                                : 'border-border hover:border-muted-foreground/40'
                                        }`}
                                    >
                                        <div className="flex gap-1">
                                            {['primary', 'secondary', 'accent'].map(
                                                (role) => (
                                                    <span
                                                        key={role}
                                                        className="size-4 rounded-full border border-black/10"
                                                        style={{
                                                            background:
                                                                resolveColor(
                                                                    manifest,
                                                                    selection,
                                                                    role,
                                                                    false,
                                                                ),
                                                        }}
                                                    />
                                                ),
                                            )}
                                        </div>
                                        <span className="text-xs font-medium">
                                            {p.label}
                                        </span>
                                    </button>
                                ))}
                            </div>

                            <div className="mt-2 flex flex-col gap-3">
                                {editableRoles.map((role) => (
                                    <div key={role} className="grid gap-1.5">
                                        <Label className="capitalize">{role}</Label>
                                        <div className="flex items-center gap-2">
                                            <Input
                                                type="color"
                                                className="h-9 w-12 p-1"
                                                value={
                                                    selection.overrides[role] ??
                                                    resolveColor(
                                                        manifest,
                                                        selection,
                                                        role,
                                                        isDark,
                                                    )
                                                }
                                                onChange={(e) =>
                                                    setOverride(role, e.target.value)
                                                }
                                            />
                                            <Input
                                                value={
                                                    selection.overrides[role] ??
                                                    resolveColor(
                                                        manifest,
                                                        selection,
                                                        role,
                                                        isDark,
                                                    )
                                                }
                                                onChange={(e) =>
                                                    setOverride(role, e.target.value)
                                                }
                                                className="font-mono text-xs"
                                            />
                                        </div>
                                    </div>
                                ))}
                            </div>
                        </div>
                    )}

                    {settingsEntry.length > 0 && (
                        <div className="flex flex-col gap-3">
                            <Label>Opciones del tema</Label>
                            {settingsEntry.map(([key, schema]) => (
                                <div key={key} className="grid gap-1.5">
                                    <Label>{schema.label}</Label>
                                    {schema.type === 'bool' ? (
                                        <button
                                            type="button"
                                            onClick={() =>
                                                setSetting(
                                                    key,
                                                    !(
                                                        selection.settings[key] ??
                                                        schema.default
                                                    ),
                                                )
                                            }
                                            className="flex items-center gap-2 text-sm"
                                        >
                                            <input
                                                type="checkbox"
                                                checked={Boolean(
                                                    selection.settings[key] ??
                                                        schema.default,
                                                )}
                                                onChange={() => {}}
                                            />
                                            {String(
                                                selection.settings[key] ??
                                                    schema.default,
                                            )}
                                        </button>
                                    ) : (
                                        <Input
                                            type="text"
                                            value={
                                                String(
                                                    selection.settings[key] ??
                                                        schema.default,
                                                )
                                            }
                                            onChange={(e) =>
                                                setSetting(key, e.target.value)
                                            }
                                        />
                                    )}
                                </div>
                            ))}
                        </div>
                    )}
                </aside>

                <main className="flex-1 overflow-auto bg-background">
                    <div
                        className="tpl-root min-h-full"
                        style={previewStyles as React.CSSProperties}
                    >
                        <PreviewSurface
                            slug={slug}
                            surface={surface}
                            settings={selection.settings}
                        />
                    </div>
                </main>
            </div>
        </>
    );
}

Preview.layout = {
    breadcrumbs: [
        { title: 'Plantillas', href: '/templates' },
    ],
};
