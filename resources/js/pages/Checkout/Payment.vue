<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardFooter, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';

// Definimos un tipo personalizado que incluye todos los campos del formulario más 'payment'
interface PaymentFormErrors {
  card_number?: string;
  card_holder?: string;
  expiry_date?: string;
  cvv?: string;
  payment?: string;
}

interface CartItem {
  id: number;
  item_id: number;
  quantity: number;
  item: {
    id: number;
    name: string;
    description?: string;
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
  address: {
    id: number;
    line_1: string;
    line_2: string | null;
    city: string;
    state: string;
    zip: string;
  };
  cartItems: CartItem[];
}

interface Props {
  cart: Cart;
}

// Definir props y extraer cart directamente para usarlo en el template
const { cart } = defineProps<Props>();

// Añadimos un campo 'payment' vacío para que TypeScript no se queje de los errores
const form = useForm({
  card_number: '',
  card_holder: '',
  expiry_date: '',
  cvv: '',
  payment: '' // Campo oculto para manejar errores generales del pago
});

const formattedCardNumber = computed({
  get: () => {
    // Format as 4 groups of 4 digits with spaces
    return form.card_number.replace(/\s/g, '').replace(/(\d{4})(?=\d)/g, '$1 ').trim();
  },
  set: (value: string) => {
    // Store only digits, limit to 19 characters (16 digits + 3 spaces)
    form.card_number = value.replace(/[^\d\s]/g, '').substring(0, 19);
  }
});

const formatExpiryDate = (value: string) => {
  // Remove non-digits
  const digits = value.replace(/\D/g, '');
  
  // Format as MM/YY
  if (digits.length <= 2) {
    return digits;
  } else {
    return `${digits.substring(0, 2)}/${digits.substring(2, 4)}`;
  }
};

const handleExpiryInput = (e: Event) => {
  const input = e.target as HTMLInputElement;
  const formatted = formatExpiryDate(input.value);
  form.expiry_date = formatted;
};

const submit = () => {
  form.post(route('checkout.process'));
};
</script>

<template>
  <AppLayout>
    <Head title="Payment" />

    <div class="max-w-7xl mx-auto py-10 px-4 sm:px-6 lg:px-8">
      <div class="mb-8">
        <h1 class="text-3xl font-bold mb-2">Payment</h1>
        <p class="text-gray-600 dark:text-gray-400">Complete your purchase by providing payment information.</p>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <div class="lg:col-span-2">
          <Card class="mb-6">
            <CardHeader>
              <CardTitle>Payment Information</CardTitle>
            </CardHeader>
            <CardContent>
              <form @submit.prevent="submit" class="space-y-6">
                <div>
                  <Label for="card_number">Card Number</Label>
                  <Input
                    id="card_number"
                    type="text"
                    v-model="formattedCardNumber"
                    placeholder="4111 1111 1111 1111"
                    class="mt-1"
                    required
                  />
                  <div v-if="form.errors.card_number" class="text-red-500 text-sm mt-1">
                    {{ form.errors.card_number }}
                  </div>
                </div>

                <div>
                  <Label for="card_holder">Card Holder Name</Label>
                  <Input
                    id="card_holder"
                    type="text"
                    v-model="form.card_holder"
                    placeholder="TEST"
                    class="mt-1"
                    required
                  />
                  <div v-if="form.errors.card_holder" class="text-red-500 text-sm mt-1">
                    {{ form.errors.card_holder }}
                  </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                  <div>
                    <Label for="expiry_date">Expiry Date</Label>
                    <Input
                      id="expiry_date"
                      type="text"
                      v-model="form.expiry_date"
                      @input="handleExpiryInput"
                      placeholder="MM/YY"
                      class="mt-1"
                      maxlength="5"
                      required
                    />
                    <div v-if="form.errors.expiry_date" class="text-red-500 text-sm mt-1">
                      {{ form.errors.expiry_date }}
                    </div>
                  </div>
                  <div>
                    <Label for="cvv">CVV</Label>
                    <Input
                      id="cvv"
                      type="text"
                      v-model="form.cvv"
                      placeholder="123"
                      class="mt-1"
                      maxlength="3"
                      required
                    />
                    <div v-if="form.errors.cvv" class="text-red-500 text-sm mt-1">
                      {{ form.errors.cvv }}
                    </div>
                  </div>
                </div>

                <div v-if="form.errors.payment" class="p-4 bg-red-50 dark:bg-red-900/20 text-red-500 rounded-md">
                  {{ form.errors.payment }}
                </div>

                <div class="text-xs text-gray-500 dark:text-gray-400 border-t pt-4 mt-4">
                  <p class="mb-2">For testing purposes, use:</p>
                  <ul class="list-disc pl-5 space-y-1">
                    <li>Card Number: 4111 1111 1111 1111</li>
                    <li>Card Holder: TEST (Success) or FAIL (Declined)</li>
                    <li>Any future expiry date (MM/YY)</li>
                    <li>Any 3-digit CVV</li>
                  </ul>
                </div>
              </form>
            </CardContent>
            <CardFooter>
              <Button class="w-full" type="button" @click="submit" :disabled="form.processing">
                Complete Order
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
                <div v-for="item in cart.cartItems" :key="item.id" class="flex items-start space-x-3 pb-3 border-b last:border-b-0 last:pb-0">
                  <div class="font-medium">{{ item.quantity }}x</div>
                  <div>
                    <div>{{ item.item.name }}</div>
                    <div class="text-gray-600 dark:text-gray-400">${{ typeof item.item.price === 'number' ? item.item.price.toFixed(2) : Number(item.item.price).toFixed(2) }}</div>
                  </div>
                </div>
                
                <div class="pt-3 pb-3 border-b">
                  <h3 class="font-medium mb-1">Shipping Address</h3>
                  <p class="text-sm text-gray-600 dark:text-gray-400">
                    {{ cart.address.line_1 }}<br>
                    <template v-if="cart.address.line_2">{{ cart.address.line_2 }}<br></template>
                    {{ cart.address.city }}, {{ cart.address.state }} {{ cart.address.zip }}
                  </p>
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
