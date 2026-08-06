import { router } from '@inertiajs/react';
import { useMemo, useState } from 'react';
import { toast } from 'sonner';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';
import { Label } from '@/components/ui/label';
import { update as updateManifest } from '@/routes/templates/manifest';
import type {
    TemplateManifest,
    TemplateSettingSchema,
} from '@/types';

/**
 * Editor del manifiesto del autor: permite reescribir los bloques settings y
 * theme de template.json como JSON, validándolos en el backend (sección 3 del
 * contrato). Las paletas manipulan directamente el modelo del manifiesto.
 */
export default function ManifestEditor({ manifest, slug }: { manifest: TemplateManifest; slug: string }) {
    const [settingsText, setSettingsText] = useState(() => JSON.stringify(manifest.settings ?? {}, null, 2));
    const [themeText, setThemeText] = useState(() => JSON.stringify(manifest.theme ?? {}, null, 2));
    const [saving, setSaving] = useState(false);

    const parsed = useMemo(() => {
        let settings: Record<string, TemplateSettingSchema> | null = null;
        let theme: unknown = null;
        let error: string | null = null;

        try {
            settings = JSON.parse(settingsText);
        } catch {
            error = 'settings: JSON inválido';
        }

        try {
            theme = JSON.parse(themeText);
        } catch {
            error = error ?? 'theme: JSON inválido';
        }

        return { settings, theme, error };
    }, [settingsText, themeText]);

    const save = () => {
        if (parsed.error) {
            toast.error(parsed.error);

            return;
        }

        setSaving(true);

        router.put(
            updateManifest.url(slug),
            {
                settings: parsed.settings ?? manifest.settings,
                theme: parsed.theme ?? manifest.theme,
            } as unknown as Record<string, string>,
            {
                preserveScroll: true,
                preserveState: true,
                onSuccess: () => {
                    toast.success('Manifiesto actualizado.');
                    router.reload({ only: ['manifest'] });
                },
                onError: (errors) => {
                    const message = Object.values(errors)[0] ?? 'Error al validar el manifiesto.';
                    toast.error(String(message));
                },
                onFinish: () => setSaving(false),
            },
        );
    };

    return (
        <Dialog>
            <DialogTrigger asChild>
                <Button variant="outline" size="sm">Manifiesto</Button>
            </DialogTrigger>
            <DialogContent className="max-h-[85vh] overflow-y-auto sm:max-w-2xl">
                <DialogHeader>
                    <DialogTitle>Editar manifiesto</DialogTitle>
                    <DialogDescription>
                        Edita los bloques <code>settings</code> y <code>theme</code> de
                        template.json. La validación de la sección 3 se aplica al guardar.
                    </DialogDescription>
                </DialogHeader>

                <div className="grid gap-4">
                    <div className="grid gap-1.5">
                        <Label>settings</Label>
                        <textarea
                            value={settingsText}
                            onChange={(e) => setSettingsText(e.target.value)}
                            rows={6}
                            spellCheck={false}
                            aria-label="settings JSON"
                            className="min-h-32 w-full resize-y rounded-md border border-border bg-background p-3 font-mono text-xs text-foreground"
                        />
                    </div>
                    <div className="grid gap-1.5">
                        <Label>theme</Label>
                        <textarea
                            value={themeText}
                            onChange={(e) => setThemeText(e.target.value)}
                            rows={10}
                            spellCheck={false}
                            aria-label="theme JSON"
                            className="min-h-40 w-full resize-y rounded-md border border-border bg-background p-3 font-mono text-xs text-foreground"
                        />
                    </div>
                    {parsed.error && (
                        <p className="text-sm text-red-600">{parsed.error}</p>
                    )}
                </div>

                <DialogFooter>
                    <Button onClick={save} disabled={saving || parsed.error !== null}>
                        {saving ? 'Guardando…' : 'Guardar manifiesto'}
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    );
}