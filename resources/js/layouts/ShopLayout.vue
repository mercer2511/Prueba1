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
import type { BreadcrumbItemType } from '@/types';
import ThemeToggle from '@/components/ThemeToggle.vue';

interface Props {
    title?: string;
    breadcrumbs?: BreadcrumbItemType[];
    showCartPreview?: boolean;
}

// Default props
withDefaults(defineProps<Props>(), {
    title: '',
    breadcrumbs: () => [],
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
    <!-- Creamos nuestra propia estructura de Shop en lugar de usar AppHeader directamente -->
    <div class="min-h-screen flex flex-col">
        <!-- Header personalizado para la tienda -->
        <header class="border-b border-gray-200 dark:border-gray-800">
            <div class="container mx-auto px-4 py-3 flex items-center justify-between">
                <!-- Logo y navegación principal -->
                <div class="flex items-center space-x-6">
                    <Link href="/" class="flex items-center space-x-2">
                        <AppLogoIcon class="h-8 w-8" />
                        <span class="text-xl font-bold">ShopCart</span>
                    </Link>
                    

                    <!-- Enlaces de navegación principal eliminados, el logo funciona como acceso a la tienda -->
                </div>
                
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
                            Iniciar sesión
                        </Link>
                        <Link :href="route('register')" class="px-4 py-2 text-sm font-medium bg-primary text-primary-foreground rounded-md hover:bg-primary/90 transition-colors">
                            Registrarse
                        </Link>
                    </div>
                </div>
            </div>
        </header>
        
        <!-- Contenido principal -->
        <main class="container mx-auto px-4 py-8 flex-grow">
            <!-- Breadcrumbs if available -->
            <div v-if="breadcrumbs && breadcrumbs.length > 0" class="flex items-center gap-2 mb-4 border-b border-sidebar-border/70 pb-3">
                <button data-slot="sidebar-trigger" class="inline-flex items-center justify-center gap-2 whitespace-nowrap rounded-md text-sm font-medium transition-all disabled:pointer-events-none disabled:opacity-50 [&_svg]:pointer-events-none [&_svg:not([class*='size-'])]:size-4 shrink-0 [&_svg]:shrink-0 outline-none focus-visible:border-ring focus-visible:ring-ring/50 focus-visible:ring-[3px] aria-invalid:ring-destructive/20 dark:aria-invalid:ring-destructive/40 aria-invalid:border-destructive hover:bg-accent hover:text-accent-foreground dark:hover:bg-accent/50 size-9 h-7 w-7 -ml-1">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-panel-left-icon">
                        <rect width="18" height="18" x="3" y="3" rx="2"></rect>
                        <path d="M9 3v18"></path>
                    </svg>
                    <span class="sr-only">Toggle Sidebar</span>
                </button>
                <nav aria-label="breadcrumb" data-slot="breadcrumb">
                    <ol data-slot="breadcrumb-list" class="text-muted-foreground flex flex-wrap items-center gap-1.5 text-sm break-words sm:gap-2.5">
                        <li v-for="(item, index) in breadcrumbs" :key="index" data-slot="breadcrumb-item" class="inline-flex items-center gap-1.5">
                            <template v-if="index === breadcrumbs.length - 1">
                                <span data-slot="breadcrumb-page" role="link" aria-disabled="true" aria-current="page" class="text-foreground font-normal">{{ item.title }}</span>
                            </template>
                            <template v-else>
                                <Link :href="item.href || '#'" class="hover:text-foreground transition-colors">{{ item.title }}</Link>
                                <li data-slot="breadcrumb-separator" role="presentation" aria-hidden="true" class="[&>svg]:size-3.5">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-chevron-right-icon">
                                        <path d="m9 18 6-6-6-6"></path>
                                    </svg>
                                </li>
                            </template>
                        </li>
                    </ol>
                </nav>
            </div>

            <!-- Título de la sección -->
            <div v-if="title" class="mb-8">
                <h1 class="text-3xl font-bold tracking-tight">{{ title }}</h1>
            </div>
            
            <!-- Contenido dinámico -->
            <slot />
        </main>
    </div>
</template>
