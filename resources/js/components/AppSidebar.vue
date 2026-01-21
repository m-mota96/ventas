<script setup lang="ts">
import NavFooter from '@/components/NavFooter.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { dashboard } from '@/routes';
import { type NavItem } from '@/types';
import { Link, usePage } from '@inertiajs/vue3';
import { Users, LayoutGrid, Store, ClipboardList, DollarSign, PackageOpen } from 'lucide-vue-next';
import AppLogo from './AppLogo.vue';
import { computed } from 'vue';

const page = usePage();
const userRole = computed(() => page.props.auth.user.roles[0].name);

const mainNavItems: NavItem[] = [
    {
        title: 'Inicio',
        href: dashboard(),
        icon: LayoutGrid,
        roles: ['admin']
    },
    {
        title: 'Sucursales',
        href: '../administrador/sucursales',
        icon: Store,
        roles: ['admin']
    },
    {
        title: 'Usuarios',
        href: '../administrador/usuarios',
        icon: Users,
        roles: ['admin']
    },
    {
        title: 'Inicio',
        href: dashboard(),
        icon: LayoutGrid,
        roles: ['user']
    },
    {
        title: 'Productos',
        href: '../usuario/productos',
        icon: ClipboardList,
        roles: ['user']
    },
    {
        title: 'Inventario',
        href: '../usuario/inventario',
        icon: PackageOpen,
        roles: ['user']
    },
    {
        title: 'Ventas',
        href: '../usuario/ventas',
        icon: DollarSign,
        roles: ['user']
    },
];

const footerNavItems: NavItem[] = [
    // {
    //     title: 'Github Repo',
    //     href: 'https://github.com/laravel/vue-starter-kit',
    //     icon: Folder,
    // },
    // {
    //     title: 'Documentation',
    //     href: 'https://laravel.com/docs/starter-kits#vue',
    //     icon: BookOpen,
    // },
];

const filteredMainNavItems = computed(() => {
    return mainNavItems.filter(item =>
        !item.roles || item.roles.includes(userRole.value)
    )
})
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link :href="dashboard()">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <NavMain :items="filteredMainNavItems" />
        </SidebarContent>

        <SidebarFooter>
            <NavFooter :items="footerNavItems" />
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
