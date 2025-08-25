<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Checkbox } from '@/components/ui/checkbox';
import InputError from '@/components/InputError.vue';
import { onMounted } from 'vue';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Mi cuenta',
        href: '/dashboard',
    },
    {
        title: 'Mis direcciones',
        href: '/addresses',
    },
    {
        title: 'Nueva dirección',
        href: '/addresses/create',
    }
];

const form = useForm({
    line_1: '',
    line_2: '',
    city: '',
    state: '',
    zip: '',
    is_default: false
});

function handleSubmit() {
    form.post(route('addresses.store'));
}

onMounted(() => {
    // Auto-focus the first input field when the component is mounted
    document.getElementById('line_1')?.focus();
});
</script>

<template>
    <Head title="Nueva Dirección" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col gap-6 p-4">
            <Card>
                <CardHeader>
                    <CardTitle>Nueva Dirección</CardTitle>
                    <CardDescription>Agrega una nueva dirección de envío</CardDescription>
                </CardHeader>
                <form @submit.prevent="handleSubmit">
                    <CardContent class="space-y-4">
                        <div class="grid gap-2">
                            <Label for="line_1">Dirección Línea 1 *</Label>
                            <Input 
                                id="line_1" 
                                v-model="form.line_1" 
                                type="text" 
                                placeholder="Calle, número exterior" 
                                required
                            />
                            <InputError :message="form.errors.line_1" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="line_2">Dirección Línea 2</Label>
                            <Input 
                                id="line_2" 
                                v-model="form.line_2" 
                                type="text" 
                                placeholder="Apartamento, suite, unidad, etc. (opcional)"
                            />
                            <InputError :message="form.errors.line_2" />
                        </div>

                        <div class="grid gap-2 md:grid-cols-2">
                            <div class="grid gap-2">
                                <Label for="city">Ciudad *</Label>
                                <Input 
                                    id="city" 
                                    v-model="form.city" 
                                    type="text" 
                                    placeholder="Ciudad" 
                                    required
                                />
                                <InputError :message="form.errors.city" />
                            </div>

                            <div class="grid gap-2">
                                <Label for="state">Estado *</Label>
                                <Input 
                                    id="state" 
                                    v-model="form.state" 
                                    type="text" 
                                    placeholder="Estado" 
                                    required
                                />
                                <InputError :message="form.errors.state" />
                            </div>
                        </div>

                        <div class="grid gap-2 md:grid-cols-2">
                            <div class="grid gap-2">
                                <Label for="zip">Código Postal *</Label>
                                <Input 
                                    id="zip" 
                                    v-model="form.zip" 
                                    type="text" 
                                    placeholder="Código postal" 
                                    required
                                />
                                <InputError :message="form.errors.zip" />
                            </div>
                            <div class="grid gap-2">
                                <!-- Espacio en blanco para mantener la alineación -->
                            </div>
                        </div>

                        <div class="flex items-center mt-6 pt-4 border-t border-border/40">
                            <div class="flex items-center space-x-3">
                                <Checkbox id="is_default" v-model:checked="form.is_default" class="h-5 w-5" />
                                <Label for="is_default" class="text-base font-medium">
                                    Establecer como dirección predeterminada
                                </Label>
                            </div>
                        </div>
                    </CardContent>
                    <CardFooter class="flex flex-col sm:flex-row justify-between gap-3 mt-4">
                        <Link :href="route('addresses.index')" class="w-full sm:w-auto">
                            <Button type="button" variant="outline" class="w-full">Cancelar</Button>
                        </Link>
                        <Button type="submit" :disabled="form.processing" class="w-full sm:w-auto">
                            Guardar dirección
                        </Button>
                    </CardFooter>
                </form>
            </Card>
        </div>
    </AppLayout>
</template>
