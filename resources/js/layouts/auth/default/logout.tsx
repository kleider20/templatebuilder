import { Form } from '@inertiajs/react';

export default function Logout() {
    return (
        <Form action="/logout" method="post" className="text-sm">
            <button
                type="submit"
                className="inline-flex items-center gap-2 rounded-md border border-border bg-background px-3 py-1.5 text-content hover:bg-surface dark:border-zinc-700"
            >
                Cerrar sesión
            </button>
        </Form>
    );
}