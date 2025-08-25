<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import WelcomeLayout from '@/layouts/WelcomeLayout.vue';
import ItemGrid from '@/components/shop/ItemGrid.vue';
import { useProductFilters } from '@/composables/useProductFilters';
import { computed } from 'vue';

interface Item {
  id: number;
  name: string;
  description: string;
  price: number;
  stock_quantity: number;
  image_path?: string;
}

interface Props {
  items: Item[];
}

const props = defineProps<Props>();

const shopItems = props.items.map(item => ({
  id: item.id,
  name: item.name,
  description: item.description,
  price: item.price,
  image_url: item.image_path,
  quantity_available: item.stock_quantity
}));

const { filters, updateSearch } = useProductFilters();

// Computed para filtrar productos por texto
const filteredItems = computed(() =>
  shopItems.filter(item =>
    item.name.toLowerCase().includes((filters.value.search || '').toLowerCase()) ||
    item.description.toLowerCase().includes((filters.value.search || '').toLowerCase())
  )
);
</script>

<template>
    <Head title="Tienda" />
    <WelcomeLayout title="Nuestros Productos">
        <div class="mb-6">
            <p class="text-gray-600 dark:text-gray-400">Explora nuestra colección de productos de alta calidad.</p>
            <div class="mt-4">
                <input 
                    type="text" 
                    class="w-full max-w-md px-4 py-2 border rounded-md" 
                    placeholder="Buscar productos..." 
                    v-model="filters.search"
                    @input="updateSearch(filters.search || '')"
                />
            </div>
        </div>
        <!-- Usar la lista filtrada -->
        <ItemGrid :items="filteredItems" />
    </WelcomeLayout>
</template>