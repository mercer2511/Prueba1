<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import WelcomeLayout from '@/layouts/WelcomeLayout.vue';
import ItemGrid from '@/components/shop/ItemGrid.vue';
import { useProductFilters } from '@/composables/useProductFilters';

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

// Transformar los datos para ajustarse a nuestros componentes
const shopItems = props.items.map(item => ({
  id: item.id,
  name: item.name,
  description: item.description,
  price: item.price,
  image_url: item.image_path,
  quantity_available: item.stock_quantity
}));

// Inicializar filtros de productos
const { filters, updateSearch } = useProductFilters();
</script>

<template>
    <Head title="Shop" />
    <WelcomeLayout title="Our Products">
        <div class="mb-6">
            <p class="text-gray-600 dark:text-gray-400">Browse our collection of high-quality products.</p>
            
            <!-- Aquí podríamos agregar un componente de búsqueda -->
            <div class="mt-4">
                <input 
                    type="text" 
                    class="w-full max-w-md px-4 py-2 border rounded-md" 
                    placeholder="Search products..." 
                    v-model="filters.search"
                    @input="updateSearch(filters.search || '')"
                />
            </div>
        </div>
        
        <!-- Grid de productos utilizando nuestro componente ItemGrid -->
        <ItemGrid :items="shopItems" />
    </WelcomeLayout>
</template>
