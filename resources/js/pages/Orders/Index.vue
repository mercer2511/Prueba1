<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import CartLayout from '@/layouts/CartLayout.vue';
import { type BreadcrumbItem } from '@/types';

const breadcrumbs: BreadcrumbItem[] = [
  {
    title: 'Mi cuenta',
    href: route('dashboard'),
  },
  {
    title: 'Mis órdenes',
    href: route('orders.index'),
  }
];

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
  orders: Order[];
}

defineProps<Props>();

const formatDate = (dateString: string) => {
  const date = new Date(dateString);
  return new Intl.DateTimeFormat('en-US', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
  }).format(date);
};

const cancelOrder = (orderId: number) => {
  if (confirm('Are you sure you want to cancel this order?')) {
    router.post(route('orders.cancel', orderId));
  }
};

const getStatusClass = (status: string) => {
  switch (status) {
    case 'pending':
      return 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-500';
    case 'processing':
      return 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-500';
    case 'completed':
      return 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-500';
    case 'cancelled':
      return 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-500';
    default:
      return 'bg-gray-100 text-gray-800 dark:bg-gray-900/30 dark:text-gray-500';
  }
};
</script>

<template>
  <CartLayout title="My Orders" :breadcrumbs="breadcrumbs">
    <Head title="My Orders" />

    <div class="flex flex-col gap-6 p-4">
      <div>
        <h1 class="text-2xl font-bold">My Orders</h1>
        <p class="text-muted-foreground mt-1">View and manage your purchase history.</p>
      </div>

      <div v-if="orders.length === 0" class="text-center py-12 bg-card rounded-lg shadow">
        <div class="mx-auto w-16 h-16 mb-4 flex items-center justify-center rounded-full bg-muted">
          <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-muted-foreground"><rect width="20" height="14" x="2" y="3" rx="2"/><path d="M8 21h8"/><path d="M12 17v4"/><circle cx="12" cy="10" r="3"/></svg>
        </div>
        <h2 class="text-xl font-semibold mb-2">You haven't placed any orders yet.</h2>
        <p class="text-muted-foreground mb-6">Start shopping to see your orders here</p>
        <Link :href="route('home')">
          <Button variant="default">Browse Products</Button>
        </Link>
      </div>

      <div v-else class="grid gap-4">
        <Card v-for="order in orders" :key="order.id" class="overflow-hidden">
          <CardHeader class="flex flex-col sm:flex-row gap-4 items-start justify-between">
            <div>
              <CardTitle class="flex flex-wrap items-center gap-2">
                <span>Order #{{ order.id }}</span>
                <span :class="['text-xs px-2 py-1 rounded-full', getStatusClass(order.status)]">
                  {{ order.status.charAt(0).toUpperCase() + order.status.slice(1) }}
                </span>
              </CardTitle>
              <p class="text-sm text-muted-foreground mt-1">
                Placed on {{ formatDate(order.created_at) }}
              </p>
            </div>
            <div class="sm:text-right w-full sm:w-auto">
              <div class="font-bold text-lg">${{ order.total.toFixed(2) }}</div>
              <div class="flex flex-wrap gap-3 mt-2">
                <Link :href="route('orders.show', order.id)">
                  <Button variant="outline" size="sm">View Details</Button>
                </Link>
                <Button 
                  v-if="order.status === 'pending'" 
                  @click="cancelOrder(order.id)" 
                  variant="destructive" 
                  size="sm"
                >
                  Cancel Order
                </Button>
              </div>
            </div>
          </CardHeader>
          <CardContent class="border-t border-border/40 pt-4">
            <div class="space-y-3">
              <div v-for="item in order.items.slice(0, 3)" :key="item.id" class="flex justify-between text-sm">
                <div class="font-medium">{{ item.quantity }}x {{ item.item.name }}</div>
                <div class="font-semibold">${{ (item.quantity * item.price).toFixed(2) }}</div>
              </div>
              <div v-if="order.items.length > 3" class="text-sm text-muted-foreground italic">
                + {{ order.items.length - 3 }} more item(s)
              </div>
            </div>
          </CardContent>
        </Card>
      </div>
    </div>
  </CartLayout>
</template>
