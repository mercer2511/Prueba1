<script setup lang="ts">
import UserInfo from '@/components/UserInfo.vue';
import { DropdownMenu, DropdownMenuContent, DropdownMenuTrigger } from '@/components/ui/dropdown-menu';
import { SidebarMenu, SidebarMenuButton, SidebarMenuItem, useSidebar } from '@/components/ui/sidebar';
import { Link, usePage } from '@inertiajs/vue3';
import { ChevronsUpDown, User } from 'lucide-vue-next';
import UserMenuContent from './UserMenuContent.vue';

const page = usePage();
const user = page.props.auth.user;
const { isMobile, state } = useSidebar();
</script>

<template>
    <SidebarMenu>
        <SidebarMenuItem>
            <!-- Show dropdown menu only if user is logged in -->
            <template v-if="user">
                <DropdownMenu>
                    <DropdownMenuTrigger as-child>
                        <SidebarMenuButton size="lg" class="data-[state=open]:bg-sidebar-accent data-[state=open]:text-sidebar-accent-foreground">
                            <UserInfo :user="user" />
                            <ChevronsUpDown class="ml-auto size-4" />
                        </SidebarMenuButton>
                    </DropdownMenuTrigger>
                    <DropdownMenuContent
                        class="w-(--reka-dropdown-menu-trigger-width) min-w-56 rounded-lg"
                        :side="isMobile ? 'bottom' : state === 'collapsed' ? 'left' : 'bottom'"
                        align="end"
                        :side-offset="4"
                    >
                        <UserMenuContent :user="user" />
                    </DropdownMenuContent>
                </DropdownMenu>
            </template>
            <!-- Show login/register buttons if user is not logged in -->
            <template v-else>
                <div class="flex flex-col space-y-2 p-2">
                    <Link :href="route('login')" class="w-full">
                        <SidebarMenuButton size="sm">
                            Iniciar sesión
                        </SidebarMenuButton>
                    </Link>
                    <Link :href="route('register')" class="w-full">
                        <SidebarMenuButton size="sm" variant="outline">
                            Registrarse
                        </SidebarMenuButton>
                    </Link>
                </div>
            </template>
        </SidebarMenuItem>
    </SidebarMenu>
</template>
