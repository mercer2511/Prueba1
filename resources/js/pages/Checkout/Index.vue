<script setup lang="ts">
import type { BreadcrumbItemType } from '@/types';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import { usePage } from '@inertiajs/vue3';
import GuestShippingForm from '@/components/GuestShippingForm.vue';
import { useGuestShippingForm } from '@/composables/useGuestShippingForm';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardFooter, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import CartLayout from '@/layouts/CartLayout.vue';

interface Address {
  id: number;
  line_1: string;
  line_2: string | null;
  city: string;
  state: string;
  zip: string;
  is_default: boolean;
}

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
  shipping_address_id: number | null;
  items: CartItem[];
}

interface Props {
  cart: Cart;
  addresses: Address[];
}

const props = defineProps<Props>();


// Detectar autenticación
const page = usePage();
const isAuthenticated = computed(() => !!(page.props.auth && page.props.auth.user));

const selectedAddressId = ref(props.addresses.find(a => a.is_default)?.id || (props.addresses.length > 0 ? props.addresses[0].id : null));

// Guest shipping form composable
const guestForm = useGuestShippingForm();

const breadcrumbs: BreadcrumbItemType[] = [
  {
    title: 'Carrito',
    href: route('cart.index'),
  },
  {
    title: 'Finalizar compra',
    href: route('checkout.index'),
  },
];

const submitShipping = () => {
  if (isAuthenticated.value) {
    if (!selectedAddressId.value) {
      alert('Por favor selecciona una dirección o agrega una nueva');
      return;
    }
    router.post(route('checkout.shipping'), {
      address_id: selectedAddressId.value
    });
  } else {
    guestForm.submit();
  }
};
</script>

<template>
  <CartLayout title="Finalizar compra" :breadcrumbs="breadcrumbs">

    <Head title="Finalizar compra" />
    <div class="mb-8">
      <p class="text-gray-600 dark:text-gray-400">Completa tu compra proporcionando la información de envío.</p>
    </div>
    <div class="max-w-7xl mx-auto py-10 px-4 sm:px-6 lg:px-8">
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <div class="lg:col-span-2">
          <Card class="mb-6">
            <CardHeader>
              <CardTitle>Dirección de envío</CardTitle>
            </CardHeader>
            <CardContent>
              <template v-if="isAuthenticated">
                <div v-if="addresses.length === 0" class="py-6 text-center">
                  <p class="mb-4 text-gray-600 dark:text-gray-400">No tienes direcciones guardadas.</p>
                  <Link :href="route('addresses.create')" class="text-[#f53003] dark:text-[#FF4433] hover:underline">
                  Agregar nueva dirección
                  </Link>
                </div>
                <div v-else class="space-y-4">
                  <div v-for="address in addresses" :key="address.id"
                    class="flex items-start space-x-3 p-4 border rounded-md"
                    :class="{ 'border-[#f53003] dark:border-[#FF4433]': selectedAddressId === address.id }">
                    <div class="flex-shrink-0">
                      <input type="radio" :id="`address-${address.id}`" name="address" :value="address.id"
                        v-model="selectedAddressId" class="h-4 w-4 text-[#f53003] dark:text-[#FF4433]" />
                    </div>
                    <div class="flex-grow">
                      <label :for="`address-${address.id}`" class="block text-sm font-medium cursor-pointer">
                        <div>
                          <span class="font-bold">{{ address.line_1 }}</span>
                          <span v-if="address.is_default"
                            class="ml-2 text-xs bg-gray-100 dark:bg-gray-800 px-2 py-0.5 rounded">Predeterminada</span>
                        </div>
                        <div v-if="address.line_2">{{ address.line_2 }}</div>
                        <div>{{ address.city }}, {{ address.state }} {{ address.zip }}</div>
                      </label>
                    </div>
                  </div>
                  <div class="pt-4 text-right">
                    <Link :href="route('addresses.create')"
                      class="text-[#f53003] dark:text-[#FF4433] hover:underline text-sm">
                    Agregar nueva dirección
                    </Link>
                  </div>
                </div>
              </template>
              <template v-else>
                <GuestShippingForm v-bind="guestForm.data" :errors="guestForm.errors" :processing="guestForm.processing"
                  @submit="guestForm.submit" />
              </template>
            </CardContent>
            <CardFooter>
              <Button class="w-full" @click="submitShipping" :disabled="isAuthenticated && !selectedAddressId">
                Continuar al pago
              </Button>
            </CardFooter>
          </Card>
        </div>

        <div class="lg:col-span-1">
          <Card>
            <CardHeader>
              <CardTitle>Resumen del pedido</CardTitle>
            </CardHeader>
            <CardContent>
              <div class="space-y-4">
                <div v-for="item in cart.items" :key="item.id"
                  class="flex items-start space-x-3 pb-3 border-b last:border-b-0 last:pb-0">
                  <div class="font-medium">{{ item.pivot.quantity }}x</div>
                  <div>
                    <div>{{ item.name }}</div>
                    <div class="text-gray-600 dark:text-gray-400">${{ typeof item.price === 'number' ?
                      item.price.toFixed(2) :
                      Number(item.price).toFixed(2) }}</div>
                  </div>
                </div>
                <div class="flex justify-between">
                  <span>Subtotal</span>
                  <span>${{ typeof cart.subtotal === 'number' ? cart.subtotal.toFixed(2) :
                    Number(cart.subtotal).toFixed(2)
                    }}</span>
                </div>
                <div class="flex justify-between">
                  <span>Impuesto (16%)</span>
                  <span>${{ typeof cart.tax === 'number' ? cart.tax.toFixed(2) : Number(cart.tax).toFixed(2) }}</span>
                </div>
                <div class="flex justify-between">
                  <span>Envío</span>
                  <span>${{ typeof cart.shipping_fee === 'number' ? cart.shipping_fee.toFixed(2) :
                    Number(cart.shipping_fee).toFixed(2) }}</span>
                </div>
                <div class="border-t pt-2 mt-2 flex justify-between font-bold">
                  <span>Total</span>
                  <span>${{ typeof cart.total === 'number' ? cart.total.toFixed(2) : Number(cart.total).toFixed(2)
                    }}</span>
                </div>
              </div>
            </CardContent>
          </Card>
        </div>
      </div>
    </div>
  </CartLayout>
</template>
