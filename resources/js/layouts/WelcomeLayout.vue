<script setup lang="ts">
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import CartPreview from '@/components/shop/CartPreview.vue';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { Button } from '@/components/ui/button';
import { DropdownMenu, DropdownMenuContent, DropdownMenuTrigger } from '@/components/ui/dropdown-menu';
import UserMenuContent from '@/components/UserMenuContent.vue';
import { useCart } from '@/composables/useCart';
import { useInitials } from '@/composables/useInitials';
import { Link, usePage } from '@inertiajs/vue3';
import ThemeToggle from '@/components/ThemeToggle.vue';

interface Props {
    title?: string;
    showCartPreview?: boolean;
}

// Default props
withDefaults(defineProps<Props>(), {
    title: '',
    showCartPreview: true
});

// Initialize cart
const { cart, itemCount, initCart } = useCart();

// Get user initials utility
const { getInitials } = useInitials();

// If cart data is available in page props, initialize with it
const page = usePage();
if (page.props.cart) {
    initCart(page.props.cart as any);
}
</script>

<template>
    <!-- Estructura limpia sin pestañas ni elementos innecesarios -->
    <div class="min-h-screen flex flex-col">
        <!-- Header simplificado -->
        <header class="border-b border-gray-200 dark:border-gray-800">
            <div class="container mx-auto px-4 py-3 flex items-center justify-between">
                <!-- Logo -->
                <Link href="/" class="flex items-center space-x-2">
                    <AppLogoIcon class="h-8 w-8" />
                    <span class="text-xl font-bold">ShopCart</span>
                </Link>
                
                <!-- Acciones de usuario y carrito -->
                <div class="flex items-center space-x-4">
                    <!-- Botón de cambio de tema -->
                    <ThemeToggle />
                    
                    <!-- Previsualización del carrito -->
                    <div v-if="showCartPreview && itemCount > 0" class="relative">
                        <CartPreview :cart-items="cart?.items || []" />
                    </div>
                    
                    <!-- Enlace al carrito -->
                    <Link href="/cart" class="p-2 rounded-full hover:bg-gray-100 dark:hover:bg-gray-800">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-shopping-cart">
                            <circle cx="8" cy="21" r="1"/>
                            <circle cx="19" cy="21" r="1"/>
                            <path d="M2.05 2.05h2l2.66 12.42a2 2 0 0 0 2 1.58h9.78a2 2 0 0 0 1.95-1.57l1.65-7.43H5.12"/>
                        </svg>
                    </Link>
                    
                    <!-- Enlaces de usuario -->
                    <div v-if="page.props.auth.user" class="flex items-center">
                        <DropdownMenu>
                            <DropdownMenuTrigger :as-child="true">
                                <Button
                                    variant="ghost"
                                    size="icon"
                                    class="relative rounded-full"
                                >
                                    <Avatar>
                                        <AvatarImage v-if="page.props.auth.user.avatar" :src="page.props.auth.user.avatar" :alt="`${page.props.auth.user.first_name} ${page.props.auth.user.last_name}`" />
                                        <AvatarFallback class="rounded-full bg-neutral-200 text-sm font-semibold text-black dark:bg-neutral-700 dark:text-white">
                                            {{ getInitials(`${page.props.auth.user.first_name} ${page.props.auth.user.last_name}`) }}
                                        </AvatarFallback>
                                    </Avatar>
                                </Button>
                            </DropdownMenuTrigger>
                            <DropdownMenuContent align="end">
                                <UserMenuContent :user="page.props.auth.user" />
                            </DropdownMenuContent>
                        </DropdownMenu>
                    </div>
                    <div v-else class="flex items-center space-x-2">
                        <Link :href="route('login')" class="px-4 py-2 text-sm font-medium rounded-md hover:bg-gray-100 dark:hover:bg-gray-800">
                            Login
                        </Link>
                        <!-- Botón de registro usando la paleta Catppuccin -->
                        <Link 
                            :href="route('register')" 
                            class="px-4 py-2 text-sm font-medium bg-primary text-primary-foreground rounded-md hover:bg-primary/90 transition-colors"
                        >
                            Register
                        </Link>
                    </div>
                </div>
            </div>
        </header>
        
        <!-- Contenido principal -->
        <main class="container mx-auto px-4 py-8 flex-grow">
            <!-- Título de la sección -->
            <div v-if="title" class="mb-8">
                <h1 class="text-3xl font-bold tracking-tight">{{ title }}</h1>
            </div>
            
            <!-- Contenido dinámico -->
            <slot />
        </main>
    </div>
</template>
