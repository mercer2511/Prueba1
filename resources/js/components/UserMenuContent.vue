<script setup lang="ts">
import UserInfo from '@/components/UserInfo.vue';
import { DropdownMenuGroup, DropdownMenuItem, DropdownMenuLabel, DropdownMenuSeparator } from '@/components/ui/dropdown-menu';
import type { User } from '@/types';
import { Link, router, usePage } from '@inertiajs/vue3';
import { LogOut, Settings, LayoutGrid } from 'lucide-vue-next';
import { computed } from 'vue';

interface Props {
    user: User | null;
}

defineProps<Props>();

// Comprueba si la página actual es parte del área del usuario (dashboard, addresses, etc.)
const isOnUserArea = computed(() => {
    // Lista de rutas que son parte del área del usuario
    const userAreaRoutes = [
        'dashboard', 
        'addresses.index', 
        'addresses.create', 
        'addresses.edit',
        'orders.index',
        'orders.show',
        'profile.edit',
        'favorites.index'
    ];
    
    // Verifica si alguna de estas rutas está activa
    return userAreaRoutes.some(routeName => route().current(routeName));
});

const handleLogout = () => {
    router.flushAll();
};
</script>

<template>
    <template v-if="user">
        <DropdownMenuLabel class="p-0 font-normal">
            <div class="flex items-center gap-2 px-1 py-1.5 text-left text-sm">
                <UserInfo :user="user" :show-email="true" />
            </div>
        </DropdownMenuLabel>
        <DropdownMenuSeparator />
        <DropdownMenuGroup>
            <!-- Solo mostramos el enlace al dashboard si NO estamos en el área del usuario -->
            <DropdownMenuItem v-if="!isOnUserArea" :as-child="true">
                <Link class="block w-full" :href="route('dashboard')" prefetch as="button">
                    <LayoutGrid class="mr-2 h-4 w-4" />
                    Panel
                </Link>
            </DropdownMenuItem>
            <DropdownMenuItem :as-child="true">
                <Link class="block w-full" :href="route('profile.edit')" prefetch as="button">
                    <Settings class="mr-2 h-4 w-4" />
                    Configuración
                </Link>
            </DropdownMenuItem>
        </DropdownMenuGroup>
        <DropdownMenuSeparator />
        <DropdownMenuItem :as-child="true">
            <Link class="block w-full" method="post" :href="route('logout')" @click="handleLogout" as="button">
                <LogOut class="mr-2 h-4 w-4" />
                Cerrar sesión
            </Link>
        </DropdownMenuItem>
    </template>
    <template v-else>
        <DropdownMenuGroup>
            <DropdownMenuItem :as-child="true">
                <Link class="block w-full" :href="route('login')" prefetch as="button">
                    Iniciar sesión
                </Link>
            </DropdownMenuItem>
            <DropdownMenuItem :as-child="true">
                <Link class="block w-full" :href="route('register')" prefetch as="button">
                    Registrarse
                </Link>
            </DropdownMenuItem>
        </DropdownMenuGroup>
    </template>
</template>
