import { ref, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import { CartSummary } from './useCart';

// Definición de tipos
export interface Address {
  id?: number;
  first_name: string;
  last_name: string;
  email: string;
  phone: string;
  line_1: string;
  line_2?: string;
  city: string;
  state: string;
  postal_code: string;
  [key: string]: FormDataConvertible;
}

export interface PaymentMethod {
  type: 'credit_card' | 'debit_card';
  card_number: string;
  card_holder: string;
  expiration_month: string;
  expiration_year: string;
  cvv: string;
  [key: string]: FormDataConvertible;
}

export type CheckoutStep = 'address' | 'payment' | 'confirmation' | 'success';

// FormDataConvertible type para compatibilidad con Inertia
type FormDataConvertible = string | number | boolean | File | Blob | null | undefined;

export function useCheckout() {
  // Estado del checkout
  const currentStep = ref<CheckoutStep>('address');
  const isLoading = ref(false);
  const error = ref<string | null>(null);
  const address = ref<Address>({
    first_name: '',
    last_name: '',
    email: '',
    phone: '',
    line_1: '',
    line_2: '',
    city: '',
    state: '',
    postal_code: ''
  });
  const paymentMethod = ref<PaymentMethod>({
    type: 'credit_card',
    card_number: '',
    card_holder: '',
    expiration_month: '',
    expiration_year: '',
    cvv: ''
  });
  const orderCompleted = ref(false);
  const orderId = ref<number | null>(null);
  
  // Estado para mantener la lista de direcciones guardadas (para usuarios registrados)
  const savedAddresses = ref<Address[]>([]);
  const selectedAddressId = ref<number | null>(null);

  // Computados
  const isAddressStep = computed(() => currentStep.value === 'address');
  const isPaymentStep = computed(() => currentStep.value === 'payment');
  const isConfirmationStep = computed(() => currentStep.value === 'confirmation');
  const isSuccessStep = computed(() => currentStep.value === 'success');
  
  const canProceedToPayment = computed(() => {
    if (selectedAddressId.value) return true;
    
    // Validación básica del formulario de dirección
    return !!(
      address.value.first_name &&
      address.value.last_name &&
      address.value.email &&
      address.value.phone &&
      address.value.line_1 &&
      address.value.city &&
      address.value.state &&
      address.value.postal_code
    );
  });
  
  const canProceedToConfirmation = computed(() => {
    // Validación básica de datos de pago
    return !!(
      paymentMethod.value.card_number &&
      paymentMethod.value.card_holder &&
      paymentMethod.value.expiration_month &&
      paymentMethod.value.expiration_year &&
      paymentMethod.value.cvv
    );
  });

  // Métodos para manejar el flujo de checkout
  const goToStep = (step: CheckoutStep) => {
    currentStep.value = step;
  };

  const goToNextStep = () => {
    switch (currentStep.value) {
      case 'address':
        currentStep.value = 'payment';
        break;
      case 'payment':
        currentStep.value = 'confirmation';
        break;
      case 'confirmation':
        processOrder();
        break;
    }
  };

  const goToPreviousStep = () => {
    switch (currentStep.value) {
      case 'payment':
        currentStep.value = 'address';
        break;
      case 'confirmation':
        currentStep.value = 'payment';
        break;
    }
  };

  // Cargar direcciones guardadas del usuario
  const loadSavedAddresses = async () => {
    isLoading.value = true;
    error.value = null;

    try {
      await router.get('/checkout/addresses', {}, {
        preserveState: true,
        onSuccess: (page) => {
          savedAddresses.value = page.props.addresses as Address[];
        },
        onError: () => {
          error.value = 'No se pudieron cargar las direcciones guardadas';
        }
      });
    } catch (e) {
      error.value = 'Ocurrió un error al cargar las direcciones';
      console.error(e);
    } finally {
      isLoading.value = false;
    }
  };

  // Guardar dirección de envío
  const saveShippingAddress = async () => {
    if (!canProceedToPayment.value) {
      error.value = 'Por favor complete todos los campos obligatorios de la dirección';
      return;
    }

    isLoading.value = true;
    error.value = null;

    const addressData = selectedAddressId.value 
      ? { address_id: selectedAddressId.value }
      : address.value;

    try {
      await router.post('/checkout/address', addressData, {
        preserveScroll: true,
        preserveState: true,
        onSuccess: () => {
          goToNextStep();
        },
        onError: (errors) => {
          error.value = 'Hay errores en el formulario de dirección';
          console.error(errors);
        }
      });
    } catch (e) {
      error.value = 'Ocurrió un error al guardar la dirección';
      console.error(e);
    } finally {
      isLoading.value = false;
    }
  };

  // Guardar método de pago y avanzar a confirmación
  const savePaymentMethod = () => {
    if (!canProceedToConfirmation.value) {
      error.value = 'Por favor complete todos los campos obligatorios de pago';
      return;
    }

    error.value = null;
    goToNextStep();
  };

  // Procesar el pedido final
  const processOrder = async () => {
    isLoading.value = true;
    error.value = null;

    try {
      await router.post('/checkout/process', {
        payment: {
          card_number: paymentMethod.value.card_number,
          card_holder: paymentMethod.value.card_holder,
          expiration_month: paymentMethod.value.expiration_month,
          expiration_year: paymentMethod.value.expiration_year,
          cvv: paymentMethod.value.cvv,
        }
      }, {
        preserveScroll: true,
        onSuccess: (page) => {
          // Casting a cualquier para manejar la propiedad potencialmente indefinida
          orderId.value = (page.props.order as any)?.id || null;
          orderCompleted.value = true;
          currentStep.value = 'success';
        },
        onError: (errors) => {
          if (errors.payment) {
            error.value = errors.payment;
          } else {
            error.value = 'Hubo un error al procesar su pedido';
          }
          console.error(errors);
        }
      });
    } catch (e) {
      error.value = 'Ocurrió un error al procesar el pedido';
      console.error(e);
    } finally {
      isLoading.value = false;
    }
  };

  // Inicializar el checkout con datos existentes si están disponibles
  const initCheckout = (initialCart?: CartSummary, initialAddress?: Address) => {
    if (initialAddress) {
      address.value = { ...initialAddress };
      if (initialAddress.id) {
        selectedAddressId.value = initialAddress.id;
      }
    }
    
    // Si hay una dirección ya seleccionada, cargar el resto
    if (initialCart?.address_id) {
      selectedAddressId.value = initialCart.address_id;
    }
    
    // Si el usuario está autenticado, cargar sus direcciones guardadas
    loadSavedAddresses();
  };

  return {
    // Estado
    currentStep,
    isLoading,
    error,
    address,
    paymentMethod,
    orderCompleted,
    orderId,
    savedAddresses,
    selectedAddressId,
    
    // Computados
    isAddressStep,
    isPaymentStep,
    isConfirmationStep,
    isSuccessStep,
    canProceedToPayment,
    canProceedToConfirmation,
    
    // Métodos
    goToStep,
    goToNextStep,
    goToPreviousStep,
    loadSavedAddresses,
    saveShippingAddress,
    savePaymentMethod,
    processOrder,
    initCheckout
  };
}
