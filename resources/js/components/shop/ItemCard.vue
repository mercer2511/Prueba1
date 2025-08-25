<script setup lang="ts">
import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from "@/components/ui/card";
import { Button } from "@/components/ui/button";
import { router } from "@inertiajs/vue3";
import { computed } from "vue";

interface Props {
  item: {
    id: number;
    name: string;
    description?: string;
    price: number;
    image_url?: string;
    quantity_available: number;
  };
}

const props = defineProps<Props>();

const formattedPrice = computed(() => {
  return new Intl.NumberFormat('es-MX', {
    style: 'currency',
    currency: 'MXN'
  }).format(props.item.price);
});

const addToCart = () => {
  try {
    router.post(route('cart.store'), {
      item_id: props.item.id,
      quantity: 1
    }, {
      preserveScroll: true,
      preserveState: true,
      onError: (errors) => {
        console.error('Error adding item to cart:', errors);
      }
    });
  } catch (error) {
    console.error('Exception when adding to cart:', error);
  }
};

const isAvailable = computed(() => {
  return props.item.quantity_available > 0;
});
</script>

<template>
  <Card class="overflow-hidden h-full flex flex-col">
    <div class="aspect-square overflow-hidden">
      <img 
        v-if="item.image_url" 
        :src="item.image_url" 
        :alt="item.name"
        class="h-full w-full object-cover transition-all hover:scale-105"
      />
      <div v-else class="h-full w-full bg-muted flex items-center justify-center">
        <span class="text-muted-foreground">Sin imagen</span>
      </div>
    </div>
    
    <CardHeader>
      <CardTitle>{{ item.name }}</CardTitle>
      <CardDescription v-if="item.description">
        {{ item.description.length > 100 
          ? item.description.substring(0, 100) + '...' 
          : item.description 
        }}
      </CardDescription>
    </CardHeader>
    
    <CardContent class="flex-grow">
      <div class="flex justify-between items-center">
        <span class="text-lg font-bold">{{ formattedPrice }}</span>
        <span v-if="!isAvailable" class="text-sm text-destructive">Agotado</span>
        <span v-else class="text-sm text-muted-foreground">Disponible: {{ item.quantity_available }}</span>
      </div>
    </CardContent>
    
    <CardFooter>
      <Button 
        class="w-full" 
        :disabled="!isAvailable"
        @click="addToCart"
      >
        Agregar al carrito
      </Button>
    </CardFooter>
  </Card>
</template>
