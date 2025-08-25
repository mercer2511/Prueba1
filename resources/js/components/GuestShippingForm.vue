<script setup lang="ts">
import { ref } from 'vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import InputError from '@/components/InputError.vue';

const emit = defineEmits(['submit']);

const guest_name = ref('');
const guest_email = ref('');
const guest_phone = ref('');
const shipping_address = ref({
	line_1: '',
	line_2: '',
	city: '',
	state: '',
	zip: ''
});

const errors = ref<Record<string, string>>({});

const validate = () => {
	errors.value = {};
	if (!guest_name.value) errors.value.guest_name = 'El nombre es obligatorio.';
	if (!guest_email.value) errors.value.guest_email = 'El correo es obligatorio.';
	else if (!/^[^@\s]+@[^@\s]+\.[^@\s]+$/.test(guest_email.value)) errors.value.guest_email = 'Correo inválido.';
	if (!guest_phone.value) errors.value.guest_phone = 'El teléfono es obligatorio.';
	if (!shipping_address.value.line_1) errors.value.line_1 = 'La dirección es obligatoria.';
	if (!shipping_address.value.city) errors.value.city = 'La ciudad es obligatoria.';
	if (!shipping_address.value.state) errors.value.state = 'El estado es obligatorio.';
	if (!shipping_address.value.zip) errors.value.zip = 'El código postal es obligatorio.';
	return Object.keys(errors.value).length === 0;
};

const handleSubmit = () => {
	if (validate()) {
		emit('submit', {
			guest_name: guest_name.value,
			guest_email: guest_email.value,
			guest_phone: guest_phone.value,
			shipping_address: { ...shipping_address.value }
		});
	}
};
</script>

<template>
	<form @submit.prevent="handleSubmit" class="space-y-6">
		<div>
			<Label for="guest_name">Nombre completo</Label>
			<Input id="guest_name" v-model="guest_name" required class="mt-1" />
			<InputError :message="errors.guest_name" class="mt-1" />
		</div>
		<div>
			<Label for="guest_email">Correo electrónico</Label>
			<Input id="guest_email" v-model="guest_email" type="email" required class="mt-1" />
			<InputError :message="errors.guest_email" class="mt-1" />
		</div>
		<div>
			<Label for="guest_phone">Teléfono</Label>
			<Input id="guest_phone" v-model="guest_phone" required class="mt-1" />
			<InputError :message="errors.guest_phone" class="mt-1" />
		</div>
		<div>
			<Label for="line_1">Dirección</Label>
			<Input id="line_1" v-model="shipping_address.line_1" required class="mt-1" />
			<InputError :message="errors.line_1" class="mt-1" />
		</div>
		<div>
			<Label for="line_2">Referencia (opcional)</Label>
			<Input id="line_2" v-model="shipping_address.line_2" class="mt-1" />
		</div>
		<div>
			<Label for="city">Ciudad</Label>
			<Input id="city" v-model="shipping_address.city" required class="mt-1" />
			<InputError :message="errors.city" class="mt-1" />
		</div>
		<div>
			<Label for="state">Estado</Label>
			<Input id="state" v-model="shipping_address.state" required class="mt-1" />
			<InputError :message="errors.state" class="mt-1" />
		</div>
		<div>
			<Label for="zip">Código postal</Label>
			<Input id="zip" v-model="shipping_address.zip" required class="mt-1" />
			<InputError :message="errors.zip" class="mt-1" />
		</div>
	</form>
</template>
