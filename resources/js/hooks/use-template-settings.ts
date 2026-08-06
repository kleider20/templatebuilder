import { useEffect, useState } from 'react';

export type TemplateSettingValue = string | boolean | number;

/**
 * Lee los settings del tema cargado por el host.
 *
 * El host (preview del builder, TemplateHost del sandbox) expone los valores en
 * `window.__TPL_SETTINGS__`, con los defaults del manifest ya mergeados. Este
 * helper devuelve el valor de un setting (o un objeto con todos) y reacciona a
 * cambios del objeto global.
 */
export function useTemplateSettings(): Record<string, TemplateSettingValue> {
    const [settings, setSettings] = useState<Record<string, TemplateSettingValue>>(() => {
        return (typeof window === 'undefined' ? {} : window.__TPL_SETTINGS__) ?? {};
    });

    useEffect(() => {
        const sync = () => {
            setSettings(window.__TPL_SETTINGS__ ?? {});
        };

        window.addEventListener('tpl:settings-changed', sync);

        return () => window.removeEventListener('tpl:settings-changed', sync);
    }, []);

    return settings;
}

/**
 * Devuelve un setting concreto. `fallback` se usa cuando el host no lo expuso.
 */
export function useTemplateSetting<T extends TemplateSettingValue>(
    key: string,
    fallback: T,
): T {
    const settings = useTemplateSettings();

    return (settings[key] as T | undefined) ?? fallback;
}