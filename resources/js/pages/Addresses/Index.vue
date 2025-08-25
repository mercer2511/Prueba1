<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/vue3';
import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { PlusCircle, MapPin, Pencil, Trash2 } from 'lucide-vue-next';

interface Address {
    id: number;
    line_1: string;
    line_2: string | null;
    city: string;
    state: string;
    zip: string;
    is_default: boolean;
}

interface Props {
    addresses: Address[];
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Mi cuenta',
        href: '/dashboard',
    },
    {
        title: 'Mis direcciones',
        href: '/addresses',
    }
];

// Variable computada para manejar direcciones de forma segura
import { computed } from 'vue';

const addresses = computed(() => props.addresses || []);
</script>

<template>
    <Head title="Mis Direcciones" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col gap-6 p-4">
            <div class="flex justify-between items-center">
                <h1 class="text-2xl font-bold">Mis Direcciones</h1>
                <Link :href="route('addresses.create')">
                    <Button>
                        <PlusCircle class="h-4 w-4 mr-1" />
                        Nueva Dirección
                    </Button>
                </Link>
            </div>
            
            <div v-if="addresses.length === 0" class="text-center py-12 bg-white dark:bg-gray-800 rounded-lg shadow">
                <MapPin class="h-12 w-12 mx-auto mb-4 text-gray-400" />
                <h2 class="text-xl font-semibold mb-2">No tienes direcciones guardadas</h2>
                <p class="text-gray-500 dark:text-gray-400 mb-4">Agrega una dirección para facilitar el proceso de compra</p>
                <Link :href="route('addresses.create')">
                    <Button>
                        <PlusCircle class="h-4 w-4 mr-1" />
                        Agregar dirección
                    </Button>
                </Link>
            </div>

            <div v-else class="grid md:grid-cols-2 lg:grid-cols-3 gap-4">
                <Card v-for="address in addresses" :key="address.id" class="relative">
                    <CardHeader>
                        <CardTitle class="flex items-center flex-wrap">
                            <span class="mr-2">{{ address.line_1 }}</span>
                            <Badge v-if="address.is_default" variant="default">Predeterminada</Badge>
                        </CardTitle>
                    </CardHeader>
                    <CardContent>
                        <p v-if="address.line_2">{{ address.line_2 }}</p>
                        <p>{{ address.city }}, {{ address.state }}</p>
                        <p>CP: {{ address.zip }}</p>
                    </CardContent>
                    <CardFooter class="flex flex-wrap gap-2 justify-end">
                        <Link v-if="!address.is_default" :href="route('addresses.default', address.id)" method="post">
                            <Button variant="outline" size="sm">
                                Establecer predeterminada
                            </Button>
                        </Link>
                        <Link :href="route('addresses.edit', address.id)">
                            <Button variant="ghost" size="sm">
                                <Pencil class="h-4 w-4 mr-1" />
                                Editar
                            </Button>
                        </Link>
                        <Link :href="route('addresses.destroy', address.id)" method="delete" as="button">
                            <Button variant="ghost" size="sm" class="text-red-600 hover:text-red-700">
                                <Trash2 class="h-4 w-4 mr-1" />
                                Eliminar
                            </Button>
                        </Link>
                    </CardFooter>
                </Card>
            </div>
        </div>
    </AppLayout>
</template>
