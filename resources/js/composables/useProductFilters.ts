import { ref, computed, watch } from 'vue';
import { router } from '@inertiajs/vue3';

// Definición de tipos
export interface ProductFilters {
  search?: string;
  category_id?: number | null;
  min_price?: number | null;
  max_price?: number | null;
  sort_by?: 'name' | 'price_asc' | 'price_desc' | 'newest' | null;
  [key: string]: FormDataConvertible;
}

export interface Category {
  id: number;
  name: string;
  description?: string;
}

// FormDataConvertible type para compatibilidad con Inertia
type FormDataConvertible = string | number | boolean | File | Blob | null | undefined;

export function useProductFilters() {
  // Estado de los filtros
  const filters = ref<ProductFilters>({
    search: '',
    category_id: null,
    min_price: null,
    max_price: null,
    sort_by: null
  });
  
  const categories = ref<Category[]>([]);
  const isLoading = ref(false);
  const error = ref<string | null>(null);
  
  // Límites de precio para el rango
  const minPriceLimit = ref<number>(0);
  const maxPriceLimit = ref<number>(10000);

  // Retraso para evitar demasiadas solicitudes al escribir en la búsqueda
  let searchTimeout: number | null = null;

  // Computados
  const hasActiveFilters = computed(() => {
    return !!(
      filters.value.search || 
      filters.value.category_id || 
      filters.value.min_price || 
      filters.value.max_price
    );
  });
  
  const activeCategoryName = computed(() => {
    if (!filters.value.category_id) return null;
    const category = categories.value.find(c => c.id === filters.value.category_id);
    return category ? category.name : null;
  });

  // Métodos
  const loadCategories = async () => {
    isLoading.value = true;
    error.value = null;

    try {
      await router.get('/api/categories', {}, {
        preserveState: true,
        onSuccess: (page) => {
          categories.value = page.props.categories as Category[];
        },
        onError: () => {
          error.value = 'No se pudieron cargar las categorías';
        }
      });
    } catch (e) {
      error.value = 'Ocurrió un error al cargar las categorías';
      console.error(e);
    } finally {
      isLoading.value = false;
    }
  };

  const applyFilters = () => {
    // Limpiar valores nulos o indefinidos para no incluirlos en la URL
    const cleanFilters: Record<string, FormDataConvertible> = {};
    
    Object.entries(filters.value).forEach(([key, value]) => {
      if (value !== null && value !== undefined && value !== '') {
        cleanFilters[key] = value;
      }
    });
    
    // Navegar con los filtros como parámetros de consulta
    router.get('/', cleanFilters, {
      preserveState: true,
      preserveScroll: true,
      replace: true, // Reemplazar en lugar de añadir al historial
    });
  };

  const updateSearch = (value: string) => {
    filters.value.search = value;
    
    // Cancelar el timeout anterior si existe
    if (searchTimeout) {
      clearTimeout(searchTimeout);
    }
    
    // Establecer un nuevo timeout para evitar llamadas innecesarias
    searchTimeout = setTimeout(() => {
      applyFilters();
    }, 500); // Esperar 500ms después de que el usuario deja de escribir
  };

  const updateCategory = (categoryId: number | null) => {
    filters.value.category_id = categoryId;
    applyFilters();
  };

  const updatePriceRange = (min: number | null, max: number | null) => {
    filters.value.min_price = min;
    filters.value.max_price = max;
    applyFilters();
  };

  const updateSorting = (sortBy: 'name' | 'price_asc' | 'price_desc' | 'newest' | null) => {
    filters.value.sort_by = sortBy;
    applyFilters();
  };

  const clearFilters = () => {
    filters.value = {
      search: '',
      category_id: null,
      min_price: null,
      max_price: null,
      sort_by: null
    };
    
    applyFilters();
  };

  // Sincronizar filtros con los parámetros de URL al inicializar
  const initFilters = (initialFilters?: ProductFilters) => {
    if (initialFilters) {
      filters.value = { ...initialFilters };
    }
    
    loadCategories();
  };

  // Vigilar cambios en los filtros para actualizar la URL
  watch(
    filters,
    () => {
      // Este watcher es para futuras implementaciones o para gestionar
      // efectos secundarios cuando cambian los filtros de manera programática
    },
    { deep: true }
  );

  return {
    // Estado
    filters,
    categories,
    isLoading,
    error,
    minPriceLimit,
    maxPriceLimit,
    
    // Computados
    hasActiveFilters,
    activeCategoryName,
    
    // Métodos
    loadCategories,
    applyFilters,
    updateSearch,
    updateCategory,
    updatePriceRange,
    updateSorting,
    clearFilters,
    initFilters
  };
}
