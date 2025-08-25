<script setup lang="ts">
import { Button } from "@/components/ui/button";
import { Link } from "@inertiajs/vue3";
import { computed } from "vue";

interface CartItem {
  id: number;
  item: {
    id: number;
    name: string;
    price: number;
    image_url?: string;
  };
  quantity: number;
}

interface Props {
  cartItems: CartItem[];
  isVisible?: boolean;
}

const props = defineProps<Props>();

const itemCount = computed(() => {
  return props.cartItems.reduce((total, item) => total + item.quantity, 0);
});

const subtotal = computed(() => {
  return props.cartItems.reduce(
    (total, item) => total + item.item.price * item.quantity, 
    0
  );
});

const formattedSubtotal = computed(() => {
  return new Intl.NumberFormat('es-MX', {
    style: 'currency',
    currency: 'MXN'
  }).format(subtotal.value);
});
</script>

<template>
  <div 
    class="bg-white border rounded-md shadow-md p-4 transition-all duration-300 ease-in-out"
    :class="{ 'translate-y-0 opacity-100': isVisible, 'translate-y-4 opacity-0': isVisible === false }"
  >
    <h3 class="font-medium mb-2">Tu carrito ({{ itemCount }})</h3>
    
    <div v-if="cartItems.length === 0" class="text-center py-4">
      <p class="text-muted-foreground">Tu carrito está vacío</p>
    </div>
    
    <div v-else>
      <ul class="space-y-2 max-h-80 overflow-auto">
        <li 
          v-for="item in cartItems" 
          :key="item.id"
          class="flex items-center gap-3 py-2 border-b last:border-b-0"
        >
          <div class="w-10 h-10 overflow-hidden rounded-md flex-shrink-0">
            <img 
              v-if="item.item.image_url" 
              :src="item.item.image_url" 
              :alt="item.item.name"
              class="h-full w-full object-cover"
            />
            <div v-else class="h-full w-full bg-muted"></div>
          </div>
          
          <div class="flex-grow overflow-hidden">
            <p class="text-sm font-medium truncate">{{ item.item.name }}</p>
            <div class="flex justify-between items-center">
              <span class="text-xs text-muted-foreground">
                {{ item.quantity }} × 
                {{ new Intl.NumberFormat('es-MX', { style: 'currency', currency: 'MXN' }).format(item.item.price) }}
              </span>
              <span class="text-xs font-medium">
                {{ new Intl.NumberFormat('es-MX', { style: 'currency', currency: 'MXN' }).format(item.item.price * item.quantity) }}
              </span>
            </div>
          </div>
        </li>
      </ul>
      
      <div class="mt-4 pt-2 border-t">
        <div class="flex justify-between mb-4">
          <span class="font-medium">Subtotal:</span>
          <span class="font-bold">{{ formattedSubtotal }}</span>
        </div>
        
        <div class="space-y-2">
          <Link href="/cart" class="block w-full">
            <Button variant="outline" class="w-full">Ver carrito</Button>
          </Link>
          <Link href="/checkout" class="block w-full">
            <Button class="w-full">Proceder al pago</Button>
          </Link>
        </div>
      </div>
    </div>
  </div>
</template>
