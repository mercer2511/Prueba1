<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { CalendarClock, Package2, ShoppingCart, Truck } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import { Card, CardHeader, CardTitle, CardDescription, CardContent, CardFooter } from '@/components/ui/card';
import { computed } from 'vue';

// Usaremos el tipo User global que ya hemos actualizado en types/index.d.ts

interface OrderItem {
  id: number;
  order_id: number;
  item_id: number;
  quantity: number;
  price: number;
  item: {
    id: number;
    name: string;
    description: string | null;
  }
}

interface Order {
  id: number;
  user_id: number;
  address_id: number;
  subtotal: number;
  tax: number;
  shipping_fee: number;
  total: number;
  status: string;
  created_at: string;
  items: OrderItem[];
}

interface Props {
  stats: {
    ordersCount: number;
    pendingOrders: number;
    totalSpent: number;
    lastOrderDate: string | null;
  };
  recentOrders: Order[];
}

defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Mi cuenta',
        href: '/dashboard',
    },
];

const page = usePage();
// Usar el tipo UserType para la propiedad user
const user = computed(() => page.props.auth.user);

function formatCurrency(amount: number): string {
    return new Intl.NumberFormat('es-MX', { style: 'currency', currency: 'MXN' }).format(amount);
}

// Esta función se usa en la tabla de pedidos recientes
function formatDate(dateStr: string): string {
    if (!dateStr) return '';
    return new Date(dateStr).toLocaleDateString('es-MX', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
}
</script>

<template>
    <Head title="Mi Cuenta" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 rounded-xl p-4 overflow-x-auto">
            <!-- Tarjeta de bienvenida -->
            <Card>
                <CardHeader>
                    <CardTitle class="text-2xl">¡Bienvenido de vuelta, {{ user.first_name }}!</CardTitle>
                    <CardDescription>Desde aquí puedes administrar tus compras, direcciones y preferencias</CardDescription>
                </CardHeader>
            </Card>
            
            <!-- Estadísticas del cliente -->
            <div class="grid auto-rows-min gap-4 md:grid-cols-4">
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Órdenes Totales</CardTitle>
                        <ShoppingCart class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ stats.ordersCount }}</div>
                    </CardContent>
                </Card>
                
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Pedidos Pendientes</CardTitle>
                        <Package2 class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ stats.pendingOrders }}</div>
                    </CardContent>
                </Card>
                
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Total Gastado</CardTitle>
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-4 w-4 text-muted-foreground">
                            <circle cx="12" cy="12" r="10"></circle>
                            <path d="M16 8h-6a2 2 0 1 0 0 4h4a2 2 0 1 1 0 4H8"></path>
                            <path d="M12 18V6"></path>
                        </svg>
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ formatCurrency(stats.totalSpent) }}</div>
                    </CardContent>
                </Card>
                
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Última Compra</CardTitle>
                        <CalendarClock class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ stats.lastOrderDate || 'Sin compras' }}</div>
                    </CardContent>
                </Card>
            </div>
            
            <!-- Acciones rápidas -->
            <Card>
                <CardHeader>
                    <CardTitle>Acciones Rápidas</CardTitle>
                    <CardDescription>Accede rápidamente a las principales funciones</CardDescription>
                </CardHeader>
                <CardContent>
                    <div class="grid gap-4 md:grid-cols-3">
                        <Link :href="route('orders.index')">
                            <Button variant="outline" class="w-full justify-start">
                                <Package2 class="mr-2 h-4 w-4" />
                                Mis Pedidos
                            </Button>
                        </Link>
                        
                        <Link :href="route('addresses.index')">
                            <Button variant="outline" class="w-full justify-start">
                                <Truck class="mr-2 h-4 w-4" />
                                Mis Direcciones
                            </Button>
                        </Link>
                        
                        <Link :href="route('profile.edit')">
                            <Button variant="outline" class="w-full justify-start">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="mr-2 h-4 w-4">
                                    <path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"></path>
                                    <circle cx="12" cy="7" r="4"></circle>
                                </svg>
                                Mi Perfil
                            </Button>
                        </Link>
                    </div>
                </CardContent>
            </Card>
            
            <!-- Pedidos recientes -->
            <Card>
                <CardHeader>
                    <CardTitle>Pedidos Recientes</CardTitle>
                    <CardDescription>Historial de tus últimas compras</CardDescription>
                </CardHeader>
                <CardContent>
                    <div v-if="recentOrders.length === 0" class="text-center py-8 text-muted-foreground">
                        <Package2 class="mx-auto h-12 w-12 mb-4 opacity-20" />
                        <p>No tienes pedidos recientes</p>
                        <Link :href="route('home')">
                            <Button variant="link">Explorar productos</Button>
                        </Link>
                    </div>
                    <div v-else class="overflow-x-auto">
                        <table class="w-full">
                            <thead>
                                <tr class="border-b">
                                    <th class="px-4 py-3 text-left">ID</th>
                                    <th class="px-4 py-3 text-left">Fecha</th>
                                    <th class="px-4 py-3 text-left">Estado</th>
                                    <th class="px-4 py-3 text-right">Total</th>
                                    <th class="px-4 py-3 text-right">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="order in recentOrders" :key="order.id" class="border-b">
                                    <td class="px-4 py-3">#{{ order.id }}</td>
                                    <td class="px-4 py-3">{{ order.created_at }}</td>
                                    <td class="px-4 py-3">
                                        <span :class="{
                                            'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300': order.status === 'processing',
                                            'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300': order.status === 'delivered',
                                            'bg-amber-100 text-amber-800 dark:bg-amber-900 dark:text-amber-300': order.status === 'shipped',
                                            'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300': order.status === 'cancelled'
                                        }" class="rounded px-2 py-1 text-xs font-medium">
                                            {{ order.status }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-right">{{ formatCurrency(order.total) }}</td>
                                    <td class="px-4 py-3 text-right">
                                        <Link :href="route('orders.show', order.id)">
                                            <Button variant="ghost" size="sm">
                                                Ver detalles
                                            </Button>
                                        </Link>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </CardContent>
                <CardFooter v-if="recentOrders.length > 0">
                    <Link :href="route('orders.index')" class="w-full">
                        <Button variant="outline" class="w-full">Ver todos los pedidos</Button>
                    </Link>
                </CardFooter>
            </Card>
        </div>
    </AppLayout>
</template>
