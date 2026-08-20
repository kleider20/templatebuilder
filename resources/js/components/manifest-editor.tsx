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
    const initialPricing = manifest.pricing ?? { type: 'free', price: null, currency: 'USD' };
    const [settingsText, setSettingsText] = useState(() => JSON.stringify(manifest.settings ?? {}, null, 2));
    const [themeText, setThemeText] = useState(() => JSON.stringify(manifest.theme ?? {}, null, 2));
    const [pricingType, setPricingType] = useState<'free' | 'premium'>(initialPricing.type);
    const [pricingPrice, setPricingPrice] = useState<string>(
        initialPricing.price != null ? String(initialPricing.price) : '',
    );
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

        const price = pricingType === 'premium' ? Number(pricingPrice) : null;

        if (pricingType === 'premium' && (price == null || Number.isNaN(price) || price <= 0)) {
            toast.error('Indica un precio válido mayor a 0 para el tema premium.');

            return;
        }

        setSaving(true);

        router.put(
            updateManifest.url(slug),
            {
                settings: parsed.settings ?? manifest.settings,
                theme: parsed.theme ?? manifest.theme,
                pricing: {
                    type: pricingType,
                    price,
                    currency: 'USD',
                },
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
                        <Label>Precio</Label>
                        <div className="flex flex-wrap items-center gap-2">
                            <Button
                                type="button"
                                size="sm"
                                variant={pricingType === 'free' ? 'default' : 'outline'}
                                onClick={() => setPricingType('free')}
                            >
                                Gratis
                            </Button>
                            <Button
                                type="button"
                                size="sm"
                                variant={pricingType === 'premium' ? 'default' : 'outline'}
                                onClick={() => setPricingType('premium')}
                            >
                                Premium
                            </Button>
                            {pricingType === 'premium' && (
                                <div className="flex items-center gap-1.5">
                                    <span className="text-sm text-muted-foreground">USD</span>
                                    <input
                                        type="number"
                                        min="0.01"
                                        step="0.01"
                                        value={pricingPrice}
                                        onChange={(e) => setPricingPrice(e.target.value)}
                                        placeholder="0.00"
                                        aria-label="Precio en USD"
                                        className="w-28 rounded-md border border-border bg-background p-2 text-sm text-foreground"
                                    />
                                </div>
                            )}
                        </div>
                        <p className="text-xs text-muted-foreground">
                            Gratis muestra «FREE»; Premium muestra el precio en el catálogo.
                        </p>
                    </div>

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