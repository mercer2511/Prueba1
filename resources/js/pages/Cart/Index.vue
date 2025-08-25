<script setup lang="ts">
import type { BreadcrumbItemType } from '@/types';
import { Head, Link, router } from '@inertiajs/vue3';
import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardFooter, CardHeader, CardTitle } from '@/components/ui/card';
import CartLayout from '@/layouts/CartLayout.vue';

interface CartItem {
  id: number;
  name: string;
  description?: string;
  price: number;
  pivot: {
    quantity: number;
    price: number;
  }
}

interface Cart {
  id: number;
  user_id: number | null;
  subtotal: number | string;
  tax: number | string;
  shipping_fee: number | string;
  total: number | string;
  items: CartItem[]; // Changed from cartItems to items based on the Cart model
}

interface Props {
  cart: Cart | null;
  subtotal: number | string;
  tax: number | string;
  shipping: number | string;
  total: number | string;
}

const props = defineProps<Props>();

const hasItems = computed(() => {
  return props.cart && props.cart.items && props.cart.items.length > 0;
});

const cartItems = computed(() => {
  return props.cart?.items || [];
});

const updateQuantity = (itemId: number, quantity: number) => {
  router.put(route('cart.update', itemId), {
    quantity: quantity
  });
};

const removeItem = (itemId: number) => {
  router.delete(route('cart.destroy', itemId));
};

const clearCart = () => {
  router.delete(route('cart.clear'));
};

const proceedToCheckout = () => {
  router.get(route('checkout.index'));
};

const breadcrumbs: BreadcrumbItemType[] = [
  {
    title: 'Carrito',
    href: route('cart.index'),
  },
];

// Detectar autenticación usando Inertia
const page = usePage();
const isAuthenticated = computed(() => !!(page.props.auth && page.props.auth.user));
</script>

<template>
  <CartLayout title="Carrito" :breadcrumbs="breadcrumbs">
    <Head title="Carrito" />

    <div class="max-w-7xl mx-auto py-10 px-4 sm:px-6 lg:px-8">
      <div class="mb-8">
        <h1 class="text-3xl font-bold mb-2">Tu carrito de compras</h1>
        <p class="text-gray-600 dark:text-gray-400">Administra los artículos en tu carrito.</p>
      </div>

      <div v-if="!hasItems" class="text-center py-16">
        <div class="mb-6">
          <svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="mx-auto text-gray-400">
            <circle cx="8" cy="21" r="1"/>
            <circle cx="19" cy="21" r="1"/>
            <path d="M2.05 2.05h2l2.66 12.42a2 2 0 0 0 2 1.58h9.78a2 2 0 0 0 1.95-1.57l1.65-7.43H5.12"/>
          </svg>
        </div>
        <p class="text-xl text-gray-600 dark:text-gray-400 mb-6">Tu carrito está vacío.</p>
        <Link :href="route('home')" class="text-[#f53003] dark:text-[#FF4433] hover:underline">Seguir comprando</Link>
      </div>

      <div v-else class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <div class="lg:col-span-2">
          <Card class="mb-6">
            <CardHeader>
              <CardTitle>Artículos en tu carrito</CardTitle>
            </CardHeader>
            <CardContent>
              <div class="space-y-4">
                <div v-for="item in cartItems" :key="item.id" class="flex flex-col sm:flex-row border-b pb-4 last:border-0 last:pb-0">
                  <div class="flex-shrink-0 w-full sm:w-24 h-24 bg-gray-100 dark:bg-gray-800 rounded-md mb-4 sm:mb-0 flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-gray-400">
                      <rect width="18" height="18" x="3" y="3" rx="2" ry="2"/>
                      <circle cx="9" cy="9" r="2"/>
                      <path d="m21 15-3.086-3.086a2 2 0 0 0-2.828 0L6 21"/>
                    </svg>
                  </div>
                  <div class="flex-grow sm:ml-6 flex flex-col">
                    <div class="flex-grow">
                      <h3 class="text-lg font-medium">{{ item.name }}</h3>
                      <p v-if="item.description" class="text-gray-600 dark:text-gray-400 text-sm">{{ item.description }}</p>
                      <p class="text-lg font-bold mt-2">${{ typeof item.price === 'number' ? item.price.toFixed(2) : Number(item.price).toFixed(2) }}</p>
                    </div>
                    <div class="flex items-center justify-between mt-4">
                      <div class="flex items-center border rounded-md">
                        <button 
                          type="button"
                          @click="updateQuantity(item.id, item.pivot.quantity - 1)"
                          class="px-3 py-1 border-r"
                          :disabled="item.pivot.quantity <= 1"
                        >
                          -
                        </button>
                        <span class="px-4 py-1">{{ item.pivot.quantity }}</span>
                        <button 
                          type="button"
                          @click="updateQuantity(item.id, item.pivot.quantity + 1)"
                          class="px-3 py-1 border-l"
                        >
                          +
                        </button>
                      </div>
                      <button 
                        @click="removeItem(item.id)" 
                        class="text-red-500 hover:text-red-700"
                      >
                        Quitar
                      </button>
                    </div>
                  </div>
                </div>
              </div>
            </CardContent>
            <CardFooter>
              <div class="flex justify-between w-full">
                <Button variant="outline" @click="clearCart">Vaciar carrito</Button>
                <Link :href="route('home')" class="inline-flex items-center text-[#f53003] dark:text-[#FF4433] hover:underline">
                  Seguir comprando
                </Link>
              </div>
            </CardFooter>
          </Card>
        </div>

        <div class="lg:col-span-1">
          <Card>
            <CardHeader>
              <CardTitle>Resumen del pedido</CardTitle>
            </CardHeader>
            <CardContent>
              <div class="space-y-2">
                <div class="flex justify-between">
                  <span>Subtotal</span>
                  <span>${{ typeof subtotal === 'number' ? subtotal.toFixed(2) : Number(subtotal).toFixed(2) }}</span>
                </div>
                <div class="flex justify-between">
                  <span>Impuesto (16%)</span>
                  <span>${{ typeof tax === 'number' ? tax.toFixed(2) : Number(tax).toFixed(2) }}</span>
                </div>
                <div class="flex justify-between">
                  <span>Envío</span>
                  <span>${{ typeof shipping === 'number' ? shipping.toFixed(2) : Number(shipping).toFixed(2) }}</span>
                </div>
                <div class="border-t pt-2 mt-2 flex justify-between font-bold">
                  <span>Total</span>
                  <span>${{ typeof total === 'number' ? total.toFixed(2) : Number(total).toFixed(2) }}</span>
                </div>
              </div>
            </CardContent>
            <CardFooter>
              <Button class="w-full" @click="proceedToCheckout" v-if="isAuthenticated">
                Ir a pagar
              </Button>
              <Button class="w-full" @click="proceedToCheckout" v-else>
                Comprar como invitado
              </Button>
            </CardFooter>
          </Card>
        </div>
      </div>
    </div>
  </CartLayout>
</template>
