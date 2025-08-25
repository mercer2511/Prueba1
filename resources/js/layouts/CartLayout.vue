<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import ShopLayout from '@/layouts/ShopLayout.vue';
import type { BreadcrumbItemType } from '@/types';

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

// Get current authentication state
const page = usePage();
const isAuthenticated = page.props.auth && page.props.auth.user;
</script>

<template>
    <!-- Use ShopLayout for non-logged-in users -->
    <ShopLayout v-if="!isAuthenticated" :title="title" :breadcrumbs="breadcrumbs" :showCartPreview="showCartPreview">
        <slot />
    </ShopLayout>
    
    <!-- Use AppLayout for logged-in users -->
    <AppLayout v-else :breadcrumbs="breadcrumbs">
        <slot />
    </AppLayout>
</template>
