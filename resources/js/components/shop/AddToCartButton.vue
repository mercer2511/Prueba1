<script setup lang="ts">
import { Button } from "@/components/ui/button";
import { router } from "@inertiajs/vue3";
import { ref, computed, watch } from "vue";

interface Props {
  itemId: number;
  maxQuantity: number;
  initialQuantity?: number;
  showQuantityControls?: boolean;
  variant?: 'default' | 'destructive' | 'outline' | 'secondary' | 'ghost' | 'link';
  size?: 'default' | 'sm' | 'lg' | 'icon';
  fullWidth?: boolean;
  loading?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
  initialQuantity: 1,
  showQuantityControls: false,
  variant: 'default',
  size: 'default',
  fullWidth: false,
  loading: false
});

const emit = defineEmits(['added', 'updated']);

const quantity = ref(props.initialQuantity);
const isSubmitting = ref(false);

// Restablecer la cantidad si cambia el initialQuantity
watch(
  () => props.initialQuantity,
  (newValue: number) => {
    quantity.value = newValue;
  }
);

const isMaxQuantity = computed(() => {
  return quantity.value >= props.maxQuantity;
});

const isMinQuantity = computed(() => {
  return quantity.value <= 1;
});

const incrementQuantity = () => {
  if (quantity.value < props.maxQuantity) {
    quantity.value++;
  }
};

const decrementQuantity = () => {
  if (quantity.value > 1) {
    quantity.value--;
  }
};

const addToCart = () => {
  if (isSubmitting.value || props.loading) return;
  
  isSubmitting.value = true;
  
  router.post('/cart/add', {
    item_id: props.itemId,
    quantity: quantity.value
  }, {
    preserveScroll: true,
    preserveState: true,
    onSuccess: () => {
      emit('added', {
        itemId: props.itemId,
        quantity: quantity.value
      });
      
      // Si no mostramos controles de cantidad, resetear a 1
      // de lo contrario, mantener la cantidad actual para posibles ajustes
      if (!props.showQuantityControls) {
        quantity.value = 1;
      }
      
      isSubmitting.value = false;
    },
    onError: () => {
      isSubmitting.value = false;
    }
  });
};
</script>

<template>
  <div class="flex items-center" :class="{ 'w-full': fullWidth }">
    <!-- Controles de cantidad si están habilitados -->
    <div v-if="showQuantityControls" class="flex items-center mr-2">
      <Button 
        variant="outline" 
        size="icon" 
        class="h-8 w-8 rounded-r-none"
        @click="decrementQuantity"
        :disabled="isMinQuantity || isSubmitting || loading"
      >
        <span class="text-lg">-</span>
      </Button>
      <div class="h-8 px-3 flex items-center justify-center border-y">
        {{ quantity }}
      </div>
      <Button 
        variant="outline" 
        size="icon" 
        class="h-8 w-8 rounded-l-none"
        @click="incrementQuantity"
        :disabled="isMaxQuantity || isSubmitting || loading"
      >
        <span class="text-lg">+</span>
      </Button>
    </div>
    
    <!-- Botón para agregar al carrito -->
    <Button 
      :variant="variant"
      :size="size"
      :class="{ 'w-full': fullWidth && !showQuantityControls }"
      :disabled="maxQuantity <= 0 || isSubmitting || loading"
      @click="addToCart"
    >
      <!-- Mostrar indicador de carga si está enviando o si loading es true -->
      <template v-if="isSubmitting || loading">
        <span class="inline-block animate-spin mr-2">
          <!-- Icono de carga simple -->
          <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-loader-2">
            <path d="M12 2v4"></path>
            <path d="M12 18v4"></path>
            <path d="m4.93 4.93 2.83 2.83"></path>
            <path d="m16.24 16.24 2.83 2.83"></path>
            <path d="M2 12h4"></path>
            <path d="M18 12h4"></path>
            <path d="m4.93 19.07 2.83-2.83"></path>
            <path d="m16.24 7.76 2.83-2.83"></path>
          </svg>
        </span>
        <span>Agregando...</span>
      </template>
      <template v-else>
        <slot name="button-content">
          Agregar al carrito
          <span v-if="showQuantityControls">&nbsp;({{ quantity }})</span>
        </slot>
      </template>
    </Button>
  </div>
</template>
