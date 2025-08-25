import { ref } from 'vue';
import { router } from '@inertiajs/vue3';

export interface GuestShippingData {
	guest_name: string;
	guest_email: string;
	guest_phone: string;
	shipping_address: {
		line_1: string;
		line_2?: string;
		city: string;
		state: string;
		zip: string;
	};
}

export function useGuestShippingForm() {
	const data = ref<GuestShippingData>({
		guest_name: '',
		guest_email: '',
		guest_phone: '',
		shipping_address: {
			line_1: '',
			line_2: '',
			city: '',
			state: '',
			zip: ''
		}
	});

	const errors = ref<Record<string, string>>({});
	const processing = ref(false);

	const validate = (): boolean => {
		errors.value = {};
		if (!data.value.guest_name) errors.value.guest_name = 'El nombre es obligatorio.';
		if (!data.value.guest_email) errors.value.guest_email = 'El correo es obligatorio.';
		else if (!/^[^@\s]+@[^@\s]+\.[^@\s]+$/.test(data.value.guest_email)) errors.value.guest_email = 'Correo inválido.';
		if (!data.value.guest_phone) errors.value.guest_phone = 'El teléfono es obligatorio.';
		if (!data.value.shipping_address.line_1) errors.value.line_1 = 'La dirección es obligatoria.';
		if (!data.value.shipping_address.city) errors.value.city = 'La ciudad es obligatoria.';
		if (!data.value.shipping_address.state) errors.value.state = 'El estado es obligatorio.';
		if (!data.value.shipping_address.zip) errors.value.zip = 'El código postal es obligatorio.';
		return Object.keys(errors.value).length === 0;
	};

	const submit = async (onSuccess?: () => void, onError?: (errs: Record<string, string>) => void) => {
		if (!validate()) {
			if (onError) onError(errors.value);
			return;
		}
		processing.value = true;
		try {
			await router.post(route('checkout.shipping'), data.value, {
				preserveScroll: true,
				onSuccess: () => {
					if (onSuccess) onSuccess();
				},
				onError: (errs) => {
					errors.value = errs;
					if (onError) onError(errs);
				},
				onFinish: () => {
					processing.value = false;
				}
			});
		} catch (e) {
			processing.value = false;
		}
	};

	return {
		data,
		errors,
		processing,
		validate,
		submit
	};
}
