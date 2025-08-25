<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle, CardFooter } from '@/components/ui/card';
import AppLayout from '@/layouts/AppLayout.vue';

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
  <AppLayout>
    <Head :title="`Order #${order.id} Details`" />

    <div class="max-w-7xl mx-auto py-10 px-4 sm:px-6 lg:px-8">
      <div class="mb-4 flex items-center justify-between">
        <div>
          <h1 class="text-3xl font-bold flex items-center gap-3">
            Order #{{ order.id }}
            <span :class="['text-xs px-2 py-1 rounded-full', getStatusClass(order.status)]">
              {{ order.status.charAt(0).toUpperCase() + order.status.slice(1) }}
            </span>
          </h1>
          <p class="text-gray-600 dark:text-gray-400">Placed on {{ formatDate(order.created_at) }}</p>
        </div>
        <div>
          <Button v-if="order.status === 'pending'" variant="destructive" @click="cancelOrder">
            Cancel Order
          </Button>
        </div>
      </div>
      
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <div class="lg:col-span-2">
          <Card class="mb-6">
            <CardHeader>
              <CardTitle>Order Items</CardTitle>
            </CardHeader>
            <CardContent>
              <div class="space-y-4">
                <div v-for="item in order.items" :key="item.id" class="flex flex-col sm:flex-row border-b pb-4 last:border-0 last:pb-0">
                  <div class="flex-shrink-0 w-full sm:w-24 h-24 bg-gray-100 dark:bg-gray-800 rounded-md mb-4 sm:mb-0 flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-gray-400">
                      <rect width="18" height="18" x="3" y="3" rx="2" ry="2"/>
                      <circle cx="9" cy="9" r="2"/>
                      <path d="m21 15-3.086-3.086a2 2 0 0 0-2.828 0L6 21"/>
                    </svg>
                  </div>
                  <div class="flex-grow sm:ml-6 flex flex-col">
                    <div class="flex-grow">
                      <h3 class="text-lg font-medium">{{ item.item.name }}</h3>
                      <p v-if="item.item.description" class="text-gray-600 dark:text-gray-400 text-sm">{{ item.item.description }}</p>
                      <div class="flex justify-between mt-2">
                        <p>Quantity: {{ item.quantity }}</p>
                        <p class="text-lg font-bold">${{ typeof item.price === 'number' ? (item.price * item.quantity).toFixed(2) : (Number(item.price) * item.quantity).toFixed(2) }}</p>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </CardContent>
          </Card>
          
          <Card>
            <CardHeader>
              <CardTitle>Shipping Address</CardTitle>
            </CardHeader>
            <CardContent>
              <p>
                {{ order.address.line_1 }}<br>
                <template v-if="order.address.line_2">{{ order.address.line_2 }}<br></template>
                {{ order.address.city }}, {{ order.address.state }} {{ order.address.zip }}
              </p>
            </CardContent>
          </Card>
        </div>

        <div class="lg:col-span-1">
          <Card>
            <CardHeader>
              <CardTitle>Order Summary</CardTitle>
            </CardHeader>
            <CardContent>
              <div class="space-y-2">
                <div class="flex justify-between">
                  <span>Subtotal</span>
                  <span>${{ typeof order.subtotal === 'number' ? order.subtotal.toFixed(2) : Number(order.subtotal).toFixed(2) }}</span>
                </div>
                <div class="flex justify-between">
                  <span>Tax (16%)</span>
                  <span>${{ typeof order.tax === 'number' ? order.tax.toFixed(2) : Number(order.tax).toFixed(2) }}</span>
                </div>
                <div class="flex justify-between">
                  <span>Shipping</span>
                  <span>${{ typeof order.shipping_fee === 'number' ? order.shipping_fee.toFixed(2) : Number(order.shipping_fee).toFixed(2) }}</span>
                </div>
                <div class="border-t pt-2 mt-2 flex justify-between font-bold">
                  <span>Total</span>
                  <span>${{ typeof order.total === 'number' ? order.total.toFixed(2) : Number(order.total).toFixed(2) }}</span>
                </div>
              </div>
            </CardContent>
            <CardFooter>
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
  </AppLayout>
</template>
