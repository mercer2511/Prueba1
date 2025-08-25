<script setup lang="ts">
import { Button } from "@/components/ui/button";
import { router } from "@inertiajs/vue3";
import { computed } from "vue";

interface Props {
  cartItem: {
    id: number;
    item: {
      id: number;
      name: string;
      price: number;
      image_url?: string;
      quantity_available: number;
    };
    quantity: number;
  };
  showControls?: boolean;
}

const props = defineProps<Props>();
const emit = defineEmits(['update']);

const formattedPrice = computed(() => {
  return new Intl.NumberFormat('es-MX', {
    style: 'currency',
    currency: 'MXN'
  }).format(props.cartItem.item.price);
});

const formattedTotal = computed(() => {
  return new Intl.NumberFormat('es-MX', {
    style: 'currency',
    currency: 'MXN'
  }).format(props.cartItem.item.price * props.cartItem.quantity);
});

const updateQuantity = (quantity: number) => {
  if (quantity <= 0) {
    removeFromCart();
    return;
  }

  if (quantity > props.cartItem.item.quantity_available) {
    return;
  }

  router.post('/cart/update', {
    cart_item_id: props.cartItem.id,
    quantity: quantity
  }, {
    preserveScroll: true,
    preserveState: true,
    onSuccess: () => {
      emit('update');
    }
  });
};

const removeFromCart = () => {
  router.post('/cart/remove', {
    cart_item_id: props.cartItem.id
  }, {
    preserveScroll: true,
    preserveState: true,
    onSuccess: () => {
      emit('update');
    }
  });
};
</script>

<template>
  <div class="flex items-start gap-4 py-4 border-b last:border-b-0">
    <div class="w-20 h-20 overflow-hidden rounded-md flex-shrink-0">
      <img 
        v-if="cartItem.item.image_url" 
        :src="cartItem.item.image_url" 
        :alt="cartItem.item.name"
        class="h-full w-full object-cover"
      />
      <div v-else class="h-full w-full bg-muted flex items-center justify-center">
        <span class="text-muted-foreground text-xs">Sin imagen</span>
      </div>
    </div>
    
    <div class="flex-grow">
      <h4 class="font-medium">{{ cartItem.item.name }}</h4>
      <p class="text-sm text-muted-foreground">{{ formattedPrice }} / unidad</p>
      
      <div class="mt-2 flex items-center justify-between">
        <div v-if="showControls" class="flex items-center">
          <Button 
            variant="outline" 
            size="icon" 
            class="h-8 w-8 rounded-r-none"
            @click="updateQuantity(cartItem.quantity - 1)"
          >
            <span class="text-lg">-</span>
          </Button>
          <div class="h-8 px-3 flex items-center justify-center border-y">
            {{ cartItem.quantity }}
          </div>
          <Button 
            variant="outline" 
            size="icon" 
            class="h-8 w-8 rounded-l-none"
            @click="updateQuantity(cartItem.quantity + 1)"
            :disabled="cartItem.quantity >= cartItem.item.quantity_available"
          >
            <span class="text-lg">+</span>
          </Button>
        </div>
        <div v-else class="text-sm">
          Cantidad: {{ cartItem.quantity }}
        </div>
        
        <div class="font-bold">
          {{ formattedTotal }}
        </div>
      </div>
      
      <div v-if="showControls" class="mt-2 flex justify-end">
        <Button 
          variant="ghost" 
          size="sm" 
          class="text-destructive hover:text-destructive"
          @click="removeFromCart"
        >
          Eliminar
        </Button>
      </div>
    </div>
  </div>
</template>
