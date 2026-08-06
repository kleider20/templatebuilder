import { Form, Head } from '@inertiajs/react';
import { Checkbox } from '@/components/ui/checkbox';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import InputError from '@/components/input-error';
import { store } from '@/routes/templates';

const surfaceLabels: Record<string, string> = {
    landing: 'Landing',
    dashboard: 'Dashboard',
    forms: 'Forms',
    auth: 'Auth',
};

type CreateProps = {
    surfaces: string[];
};

export default function Create({ surfaces }: CreateProps) {
    return (
        <>
            <Head title="Crear plantilla" />

            <div className="mx-auto w-full max-w-2xl p-4 sm:p-6">
                <Card>
                    <CardHeader>
                        <CardTitle>Nueva plantilla</CardTitle>
                        <CardDescription>
                            Define el nombre, el slug y las superficies del tema.
                        </CardDescription>
                    </CardHeader>

                    <CardContent>
                        <Form
                            action={store.url()}
                            method="post"
                            className="space-y-6"
                        >
                            {({ errors, processing }) => (
                                <>
                                    <div className="grid gap-2">
                                        <Label htmlFor="name">Nombre</Label>
                                        <Input
                                            id="name"
                                            name="name"
                                            placeholder="Aurora"
                                            required
                                        />
                                        <InputError message={errors.name} />
                                    </div>

                                    <div className="grid gap-2">
                                        <Label htmlFor="slug">Slug</Label>
                                        <Input
                                            id="slug"
                                            name="slug"
                                            placeholder="aurora"
                                            required
                                        />
                                        <InputError message={errors.slug} />
                                    </div>

                                    <div className="grid gap-3">
                                        <Label>Superficies</Label>
                                        {surfaces.map((surface) => (
                                            <label
                                                key={surface}
                                                className="flex items-center gap-3 text-sm"
                                            >
                                                <Checkbox name="types" value={surface} />
                                                {surfaceLabels[surface] ?? surface}
                                            </label>
                                        ))}
                                        <InputError message={errors.types} />
                                    </div>

                                    <div className="flex items-center gap-2">
                                        <Checkbox name="with_css" value="1" />
                                        <Label htmlFor="with_css">
                                            Generar CSS custom
                                        </Label>
                                    </div>

                                    <div>
                                        <Button disabled={processing}>
                                            Crear
                                        </Button>
                                    </div>
                                </>
                            )}
                        </Form>
                    </CardContent>
                </Card>
            </div>
        </>
    );
}

Create.layout = {
    breadcrumbs: [
        { title: 'Plantillas', href: '/templates' },
        { title: 'Crear' },
    ],
};
