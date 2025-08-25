<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import AppLayout from '@/layouts/AppLayout.vue';

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
  <AppLayout>
    <Head title="My Orders" />

    <div class="max-w-7xl mx-auto py-10 px-4 sm:px-6 lg:px-8">
      <div class="mb-8">
        <h1 class="text-3xl font-bold mb-2">My Orders</h1>
        <p class="text-gray-600 dark:text-gray-400">View and manage your purchase history.</p>
      </div>

      <div v-if="orders.length === 0" class="text-center py-16">
        <p class="text-xl text-gray-600 dark:text-gray-400 mb-6">You haven't placed any orders yet.</p>
        <Link :href="route('home')" class="text-[#f53003] dark:text-[#FF4433] hover:underline">Browse Products</Link>
      </div>

      <div v-else class="space-y-6">
        <Card v-for="order in orders" :key="order.id" class="overflow-hidden">
          <CardHeader class="flex flex-row items-start justify-between">
            <div>
              <CardTitle class="flex items-center gap-2">
                Order #{{ order.id }}
                <span :class="['text-xs px-2 py-1 rounded-full', getStatusClass(order.status)]">
                  {{ order.status.charAt(0).toUpperCase() + order.status.slice(1) }}
                </span>
              </CardTitle>
              <p class="text-sm text-gray-500 dark:text-gray-400">
                Placed on {{ formatDate(order.created_at) }}
              </p>
            </div>
            <div class="text-right">
              <div class="font-bold">${{ order.total.toFixed(2) }}</div>
              <div class="flex space-x-2 mt-2">
                <Link :href="route('orders.show', order.id)" class="text-sm text-[#f53003] dark:text-[#FF4433] hover:underline">
                  View Details
                </Link>
                <button 
                  v-if="order.status === 'pending'" 
                  @click="cancelOrder(order.id)" 
                  class="text-sm text-red-600 dark:text-red-400 hover:underline"
                >
                  Cancel Order
                </button>
              </div>
            </div>
          </CardHeader>
          <CardContent>
            <div class="space-y-2">
              <div v-for="item in order.items.slice(0, 3)" :key="item.id" class="flex justify-between text-sm">
                <div>{{ item.quantity }}x {{ item.item.name }}</div>
                <div>${{ (item.quantity * item.price).toFixed(2) }}</div>
              </div>
              <div v-if="order.items.length > 3" class="text-sm text-gray-500 dark:text-gray-400">
                + {{ order.items.length - 3 }} more item(s)
              </div>
            </div>
          </CardContent>
        </Card>
      </div>
    </div>
  </AppLayout>
</template>
