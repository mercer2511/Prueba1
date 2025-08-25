<script setup lang="ts">
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import AuthLayout from '@/layouts/AuthLayout.vue';
import { Form, Head } from '@inertiajs/vue3';
import { LoaderCircle } from 'lucide-vue-next';

defineProps<{
    status?: string;
}>();
</script>

<template>
    <AuthLayout title="Verifica tu correo electrónico" description="Por favor verifica tu dirección de correo electrónico para continuar">
        <Head title="Verifica tu correo electrónico" />

        <div class="mb-4 text-sm text-gray-600 dark:text-gray-400">
            Antes de continuar, por favor revisa tu correo electrónico para el enlace de verificación.
            Si no recibiste el correo electrónico, podemos enviarte otro.
        </div>

        <Form method="post" :action="route('verification.send')" v-slot="{ processing }">
            <Button :disabled="processing">
                <LoaderCircle v-if="processing" class="w-4 h-4 animate-spin" />
                Reenviar correo de verificación
            </Button>
        </Form>

        <Form method="post" :action="route('logout')" class="mt-4">
            <Button variant="ghost" class="w-full">
                Cerrar sesión
            </Button>
        </Form>
    </AuthLayout>
</template>
