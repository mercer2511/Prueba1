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
                <Card v-for="address in addresses" :key="address.id" class="relative flex flex-col h-full">
                    <CardHeader>
                        <div class="flex items-start justify-between">
                            <CardTitle class="flex items-center flex-wrap">
                                <span class="break-words">{{ address.line_1 }}</span>
                            </CardTitle>
                            <Badge v-if="address.is_default" variant="default" class="ml-2 whitespace-nowrap">
                                Predeterminada
                            </Badge>
                        </div>
                    </CardHeader>
                    <CardContent class="flex-grow">
                        <p v-if="address.line_2" class="mb-1 break-words text-sm">{{ address.line_2 }}</p>
                        <p class="mb-1 text-sm">{{ address.city }}, {{ address.state }}</p>
                        <p class="text-sm">CP: {{ address.zip }}</p>
                    </CardContent>
                    <CardFooter class="flex flex-col gap-3 pt-3 border-t border-border/40">
                        <!-- Sección para establecer como predeterminada -->
                        <div v-if="!address.is_default" class="w-full">
                            <Link :href="route('addresses.default', address.id)" method="post" class="w-full">
                                <Button variant="outline" size="sm" class="w-full">
                                    <MapPin class="h-4 w-4 mr-1.5" />
                                    Establecer predeterminada
                                </Button>
                            </Link>
                        </div>
                        
                        <!-- Acciones de edición y eliminación -->
                        <div class="flex w-full justify-between gap-2">
                            <Link :href="route('addresses.edit', address.id)" class="flex-1">
                                <Button variant="ghost" size="sm" class="w-full">
                                    <Pencil class="h-4 w-4 mr-1" />
                                    Editar
                                </Button>
                            </Link>
                            <Link :href="route('addresses.destroy', address.id)" method="delete" as="button" class="flex-1">
                                <Button variant="ghost" size="sm" class="w-full text-destructive hover:text-destructive hover:bg-destructive/10">
                                    <Trash2 class="h-4 w-4 mr-1" />
                                    Eliminar
                                </Button>
                            </Link>
                        </div>
                    </CardFooter>
                </Card>
            </div>
        </div>
    </AppLayout>
</template>
