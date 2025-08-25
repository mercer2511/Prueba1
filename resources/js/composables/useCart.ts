import { ref, computed } from 'vue';
import { router } from '@inertiajs/vue3';

// Definición de tipos
export interface CartItem {
  id: number;
  item: {
    id: number;
    name: string;
    price: number;
    image_url?: string;
    quantity_available: number;
  };
  quantity: number;
}

export interface CartSummary {
  id: number;
  subtotal: number;
  tax: number;
  shipping_fee: number;
  total: number;
  items: CartItem[];
  address_id?: number;
}

export interface AddToCartParams {
  item_id: number;
  quantity: number;
  [key: string]: FormDataConvertible;
}

export interface UpdateCartItemParams {
  cart_item_id: number;
  quantity: number;
  [key: string]: FormDataConvertible;
}

// Inertia FormDataConvertible type
type FormDataConvertible = string | number | boolean | File | Blob | null | undefined;

export function useCart() {
  // Estado del carrito
  const cart = ref<CartSummary | null>(null);
  const isLoading = ref(false);
  const error = ref<string | null>(null);

  // Computados
  const itemCount = computed(() => {
    if (!cart.value?.items) return 0;
    return cart.value.items.reduce((total, item) => total + item.quantity, 0);
  });

  const formattedSubtotal = computed(() => {
    return formatPrice(cart.value?.subtotal || 0);
  });

  const formattedTax = computed(() => {
    return formatPrice(cart.value?.tax || 0);
  });

  const formattedShipping = computed(() => {
    return formatPrice(cart.value?.shipping_fee || 0);
  });

  const formattedTotal = computed(() => {
    return formatPrice(cart.value?.total || 0);
  });

  // Métodos
  const getCart = async () => {
    isLoading.value = true;
    error.value = null;

    try {
      await router.get('/cart', {}, {
        preserveState: true,
        onSuccess: (page) => {
          cart.value = page.props.cart as CartSummary;
        },
        onError: (errors) => {
          error.value = 'No se pudo cargar el carrito';
          console.error(errors);
        }
      });
    } catch (e) {
      error.value = 'Ocurrió un error al cargar el carrito';
      console.error(e);
    } finally {
      isLoading.value = false;
    }
  };

  const addToCart = async (params: AddToCartParams) => {
    isLoading.value = true;
    error.value = null;

    try {
      await router.post('/cart/add', params, {
        preserveScroll: true,
        preserveState: true,
        onSuccess: (page) => {
          cart.value = page.props.cart as CartSummary;
        },
        onError: (errors) => {
          error.value = errors.quantity || 'No se pudo agregar al carrito';
          console.error(errors);
        }
      });
    } catch (e) {
      error.value = 'Ocurrió un error al agregar el producto';
      console.error(e);
    } finally {
      isLoading.value = false;
    }
  };

  const updateCartItem = async (params: UpdateCartItemParams) => {
    isLoading.value = true;
    error.value = null;

    try {
      await router.post('/cart/update', params, {
        preserveScroll: true,
        preserveState: true,
        onSuccess: (page) => {
          cart.value = page.props.cart as CartSummary;
        },
        onError: (errors) => {
          error.value = errors.quantity || 'No se pudo actualizar la cantidad';
          console.error(errors);
        }
      });
    } catch (e) {
      error.value = 'Ocurrió un error al actualizar el carrito';
      console.error(e);
    } finally {
      isLoading.value = false;
    }
  };

  const removeCartItem = async (cartItemId: number) => {
    isLoading.value = true;
    error.value = null;

    try {
      await router.post('/cart/remove', { cart_item_id: cartItemId }, {
        preserveScroll: true,
        preserveState: true,
        onSuccess: (page) => {
          cart.value = page.props.cart as CartSummary;
        },
        onError: (errors) => {
          error.value = 'No se pudo eliminar el producto';
          console.error(errors);
        }
      });
    } catch (e) {
      error.value = 'Ocurrió un error al eliminar el producto';
      console.error(e);
    } finally {
      isLoading.value = false;
    }
  };

  const clearCart = async () => {
    isLoading.value = true;
    error.value = null;

    try {
      await router.post('/cart/clear', {}, {
        preserveScroll: true,
        preserveState: true,
        onSuccess: (page) => {
          cart.value = page.props.cart as CartSummary;
        },
        onError: () => {
          error.value = 'No se pudo vaciar el carrito';
        }
      });
    } catch (e) {
      error.value = 'Ocurrió un error al vaciar el carrito';
      console.error(e);
    } finally {
      isLoading.value = false;
    }
  };

  // Función de ayuda para formatear precios
  const formatPrice = (price: number): string => {
    return new Intl.NumberFormat('es-MX', {
      style: 'currency',
      currency: 'MXN'
    }).format(price);
  };

  // Inicialización - cargar el carrito si está disponible en las props
  const initCart = (initialCart?: CartSummary) => {
    if (initialCart) {
      cart.value = initialCart;
    } else {
      getCart();
    }
  };

  return {
    cart,
    isLoading,
    error,
    itemCount,
    formattedSubtotal,
    formattedTax,
    formattedShipping,
    formattedTotal,
    getCart,
    addToCart,
    updateCartItem,
    removeCartItem,
    clearCart,
    formatPrice,
    initCart
  };
}
