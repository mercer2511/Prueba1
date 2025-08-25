<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle, CardFooter } from '@/components/ui/card';
import CartLayout from '@/layouts/CartLayout.vue';
import { type BreadcrumbItem } from '@/types';

interface OrderItem {
  id: number;
  order_id: number;
  item_id: number;
  quantity: number;
  price: number | string;
  item: {
    id: number;
    name: string;
    description: string | null;
  }
}

interface Address {
  id: number;
  line_1: string;
  line_2: string | null;
  city: string;
  state: string;
  zip: string;
}

interface Order {
  id: number;
  user_id: number;
  address_id: number;
  subtotal: number | string;
  tax: number | string;
  shipping_fee: number | string;
  total: number | string;
  status: string;
  created_at: string;
  items: OrderItem[];
  address: Address;
}

interface Props {
  order: Order;
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
  {
    title: 'Mi cuenta',
    href: route('dashboard'),
  },
  {
    title: 'Mis órdenes',
    href: route('orders.index'),
  },
  {
    title: `Orden #${props.order.id}`,
    href: route('orders.show', props.order.id),
  }
];

const formatDate = (dateString: string) => {
  const date = new Date(dateString);
  return new Intl.DateTimeFormat('en-US', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  }).format(date);
};

const cancelOrder = () => {
  if (confirm('Are you sure you want to cancel this order?')) {
    router.post(route('orders.cancel', props.order.id));
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
  <CartLayout :title="`Order #${order.id} Details`" :breadcrumbs="breadcrumbs">
    <Head :title="`Order #${order.id} Details`" />

    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
          <h1 class="text-2xl sm:text-3xl font-bold">
            <div class="flex flex-wrap items-center gap-3">
              <span>Order #{{ order.id }}</span>
              <span :class="['text-xs px-2 py-1 rounded-full', getStatusClass(order.status)]">
                {{ order.status.charAt(0).toUpperCase() + order.status.slice(1) }}
              </span>
            </div>
          </h1>
          <p class="text-muted-foreground mt-1">Placed on {{ formatDate(order.created_at) }}</p>
        </div>
        <div>
          <Button v-if="order.status === 'pending'" variant="destructive" @click="cancelOrder">
            Cancel Order
          </Button>
        </div>
      </div>
      
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
          <Card>
            <CardHeader class="pb-3">
              <CardTitle>Order Items</CardTitle>
            </CardHeader>
            <CardContent>
              <div class="divide-y">
                <div v-for="item in order.items" :key="item.id" class="py-4 first:pt-0 last:pb-0 flex flex-col sm:flex-row gap-4">
                  <div class="flex-shrink-0 w-full sm:w-24 h-24 bg-muted rounded-md flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-muted-foreground">
                      <rect width="18" height="18" x="3" y="3" rx="2" ry="2"/>
                      <circle cx="9" cy="9" r="2"/>
                      <path d="m21 15-3.086-3.086a2 2 0 0 0-2.828 0L6 21"/>
                    </svg>
                  </div>
                  <div class="flex-grow flex flex-col justify-between">
                    <div>
                      <h3 class="text-lg font-medium">{{ item.item.name }}</h3>
                      <p v-if="item.item.description" class="text-muted-foreground text-sm mt-1">{{ item.item.description }}</p>
                    </div>
                    <div class="flex justify-between items-end mt-2">
                      <p class="text-muted-foreground">Quantity: <span class="font-medium">{{ item.quantity }}</span></p>
                      <p class="text-lg font-bold">${{ typeof item.price === 'number' ? (item.price * item.quantity).toFixed(2) : (Number(item.price) * item.quantity).toFixed(2) }}</p>
                    </div>
                  </div>
                </div>
              </div>
            </CardContent>
          </Card>
          
          <Card>
            <CardHeader class="pb-3">
              <CardTitle>Shipping Address</CardTitle>
            </CardHeader>
            <CardContent>
              <div class="p-3 bg-muted/50 rounded-md">
                <p class="font-medium">
                  {{ order.address.line_1 }}<br>
                  <template v-if="order.address.line_2">{{ order.address.line_2 }}<br></template>
                  {{ order.address.city }}, {{ order.address.state }} {{ order.address.zip }}
                </p>
              </div>
            </CardContent>
          </Card>
        </div>

        <div class="lg:col-span-1">
          <Card>
            <CardHeader class="pb-3">
              <CardTitle>Order Summary</CardTitle>
            </CardHeader>
            <CardContent>
              <div class="space-y-3">
                <div class="flex justify-between text-sm">
                  <span class="text-muted-foreground">Subtotal</span>
                  <span class="font-medium">${{ typeof order.subtotal === 'number' ? order.subtotal.toFixed(2) : Number(order.subtotal).toFixed(2) }}</span>
                </div>
                <div class="flex justify-between text-sm">
                  <span class="text-muted-foreground">Tax (16%)</span>
                  <span class="font-medium">${{ typeof order.tax === 'number' ? order.tax.toFixed(2) : Number(order.tax).toFixed(2) }}</span>
                </div>
                <div class="flex justify-between text-sm">
                  <span class="text-muted-foreground">Shipping</span>
                  <span class="font-medium">${{ typeof order.shipping_fee === 'number' ? order.shipping_fee.toFixed(2) : Number(order.shipping_fee).toFixed(2) }}</span>
                </div>
                <div class="border-t pt-3 mt-1 flex justify-between">
                  <span class="font-semibold text-lg">Total</span>
                  <span class="font-bold text-lg">${{ typeof order.total === 'number' ? order.total.toFixed(2) : Number(order.total).toFixed(2) }}</span>
                </div>
              </div>
            </CardContent>
            <CardFooter class="border-t">
              <Link :href="route('orders.index')" class="w-full">
                <Button class="w-full" variant="outline">
                  Back to Orders
                </Button>
              </Link>
            </CardFooter>
          </Card>
        </div>
      </div>
    </div>
  </CartLayout>
</template>
