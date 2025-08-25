<script setup lang="ts">
import ItemCard from "@/components/shop/ItemCard.vue";

interface Item {
  id: number;
  name: string;
  description?: string;
  price: number;
  image_url?: string;
  quantity_available: number;
}

interface Props {
  items: Item[];
  columns?: number;
}

const props = defineProps<Props>();

// Default to 3 columns if not specified
const gridColumns = props.columns || 3;
</script>

<template>
  <div class="w-full">
    <div v-if="items.length === 0" class="text-center py-8">
      <p class="text-muted-foreground">No hay productos disponibles</p>
    </div>
    
    <div 
      v-else 
      class="grid gap-4"
      :class="{
        'grid-cols-1 md:grid-cols-2 lg:grid-cols-3': gridColumns === 3,
        'grid-cols-1 md:grid-cols-2 lg:grid-cols-4': gridColumns === 4,
        'grid-cols-1 md:grid-cols-2': gridColumns === 2
      }"
    >
      <ItemCard 
        v-for="item in items" 
        :key="item.id" 
        :item="item" 
      />
    </div>
  </div>
</template>
