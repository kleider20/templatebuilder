export interface TemplateComponentRole {
    role: string;
    path: string;
}

export interface TemplateSurfaceComponents {
    [role: string]: string;
}

export interface TemplateManifest {
    name: string;
    slug: string;
    type: string[];
    version: string;
    author: string;
    description: string;
    thumbnail: string | null;
    home: string;
    components: Record<string, TemplateSurfaceComponents>;
    theme: {
        roles: string[];
        default: string;
        palettes: TemplatePalette[];
    } | null;
    settings: Record<string, TemplateSettingSchema>;
    pricing: TemplatePricing;
    dependencies: Record<string, string>;
    requires: Record<string, string>;
    exports: {
        bundle: string;
        styles: string;
    };
}

export interface TemplatePalette {
    id: string;
    label: string;
    colors: Record<string, string>;
    dark?: Record<string, string>;
}

export interface TemplateSettingSchema {
    type: 'text' | 'bool' | 'number' | 'select';
    label: string;
    default: string | boolean | number;
    options?: { value: string; label: string }[];
}

export interface TemplatePricing {
    type: 'free' | 'premium';
    price: number | null;
    currency: string;
}

export interface TemplateTreeFile {
    name: string;
    type: 'file';
    path: string;
}

export interface TemplateCatalogItem {
    name: string;
    slug: string;
    version: string;
    author: string;
    description: string;
    types: string[];
    thumbnail: string | null;
    home: string;
    settings: Record<string, TemplateSettingSchema>;
    theme: {
        roles: string[];
        default: string;
        palettes: TemplatePalette[];
    } | null;
    pricing: TemplatePricing;
}

export interface ThemeApplicationInfo {
    slug: string;
    name: string;
    home: string;
    components: Record<string, TemplateSurfaceComponents>;
    exports: {
        bundle: string;
        styles: string;
    };
}

export type ThemeApplications = Partial<
    Record<'landing' | 'dashboard' | 'forms' | 'auth', ThemeApplicationInfo | null>
>;

export interface TemplateVersionRecord {
    id: number;
    slug: string;
    version: string;
    manifest: TemplateManifest;
    zip_path: string;
    checksum: string;
    created_at: string;
    download_url: string;
}
