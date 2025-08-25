<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardFooter, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';

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

const selectedAddressId = ref(props.addresses.find(a => a.is_default)?.id || (props.addresses.length > 0 ? props.addresses[0].id : null));

const submitShipping = () => {
  if (!selectedAddressId.value) {
    alert('Please select an address or add a new one');
    return;
  }

  router.post(route('checkout.shipping'), {
    address_id: selectedAddressId.value
  });
};
</script>

<template>
  <AppLayout>
    <Head title="Checkout" />

    <div class="max-w-7xl mx-auto py-10 px-4 sm:px-6 lg:px-8">
      <div class="mb-8">
        <h1 class="text-3xl font-bold mb-2">Checkout</h1>
        <p class="text-gray-600 dark:text-gray-400">Complete your purchase by providing shipping information.</p>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <div class="lg:col-span-2">
          <Card class="mb-6">
            <CardHeader>
              <CardTitle>Shipping Address</CardTitle>
            </CardHeader>
            <CardContent>
              <div v-if="addresses.length === 0" class="py-6 text-center">
                <p class="mb-4 text-gray-600 dark:text-gray-400">You don't have any saved addresses.</p>
                <Link :href="route('addresses.create')" class="text-[#f53003] dark:text-[#FF4433] hover:underline">
                  Add a New Address
                </Link>
              </div>
              <div v-else class="space-y-4">
                <div v-for="address in addresses" :key="address.id" class="flex items-start space-x-3 p-4 border rounded-md" :class="{ 'border-[#f53003] dark:border-[#FF4433]': selectedAddressId === address.id }">
                  <div class="flex-shrink-0">
                    <input
                      type="radio"
                      :id="`address-${address.id}`"
                      name="address"
                      :value="address.id"
                      v-model="selectedAddressId"
                      class="h-4 w-4 text-[#f53003] dark:text-[#FF4433]"
                    />
                  </div>
                  <div class="flex-grow">
                    <label :for="`address-${address.id}`" class="block text-sm font-medium cursor-pointer">
                      <div>
                        <span class="font-bold">{{ address.line_1 }}</span>
                        <span v-if="address.is_default" class="ml-2 text-xs bg-gray-100 dark:bg-gray-800 px-2 py-0.5 rounded">Default</span>
                      </div>
                      <div v-if="address.line_2">{{ address.line_2 }}</div>
                      <div>{{ address.city }}, {{ address.state }} {{ address.zip }}</div>
                    </label>
                  </div>
                </div>
                <div class="pt-4 text-right">
                  <Link :href="route('addresses.create')" class="text-[#f53003] dark:text-[#FF4433] hover:underline text-sm">
                    Add a New Address
                  </Link>
                </div>
              </div>
            </CardContent>
            <CardFooter>
              <Button class="w-full" @click="submitShipping" :disabled="!selectedAddressId">
                Continue to Payment
              </Button>
            </CardFooter>
          </Card>
        </div>

        <div class="lg:col-span-1">
          <Card>
            <CardHeader>
              <CardTitle>Order Summary</CardTitle>
            </CardHeader>
            <CardContent>
              <div class="space-y-4">
                <div v-for="item in cart.items" :key="item.id" class="flex items-start space-x-3 pb-3 border-b last:border-b-0 last:pb-0">
                  <div class="font-medium">{{ item.pivot.quantity }}x</div>
                  <div>
                    <div>{{ item.name }}</div>
                    <div class="text-gray-600 dark:text-gray-400">${{ typeof item.price === 'number' ? item.price.toFixed(2) : Number(item.price).toFixed(2) }}</div>
                  </div>
                </div>
                <div class="flex justify-between">
                  <span>Subtotal</span>
                  <span>${{ typeof cart.subtotal === 'number' ? cart.subtotal.toFixed(2) : Number(cart.subtotal).toFixed(2) }}</span>
                </div>
                <div class="flex justify-between">
                  <span>Tax (16%)</span>
                  <span>${{ typeof cart.tax === 'number' ? cart.tax.toFixed(2) : Number(cart.tax).toFixed(2) }}</span>
                </div>
                <div class="flex justify-between">
                  <span>Shipping</span>
                  <span>${{ typeof cart.shipping_fee === 'number' ? cart.shipping_fee.toFixed(2) : Number(cart.shipping_fee).toFixed(2) }}</span>
                </div>
                <div class="border-t pt-2 mt-2 flex justify-between font-bold">
                  <span>Total</span>
                  <span>${{ typeof cart.total === 'number' ? cart.total.toFixed(2) : Number(cart.total).toFixed(2) }}</span>
                </div>
              </div>
            </CardContent>
          </Card>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
