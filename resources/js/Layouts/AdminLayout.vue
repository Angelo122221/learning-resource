<script setup>
import ProductLayout from '@/Layouts/ProductLayout.vue';
import { Link } from '@inertiajs/vue3';

const navItems = [
    { label: 'Resources', href: '/admin/resources', active: 'admin.resources' },
    { label: 'Analytics', href: '/admin/analytics', active: 'admin.analytics' },
    { label: 'Users', href: '/admin/users', active: 'admin.users' },
];
</script>

<template>
    <ProductLayout
        :nav-items="navItems"
        home-href="/admin/resources"
        brand-label="Learning Resources"
        brand-caption="Admin Control Center"
    >
        <template #nav-actions>
            <Link href="/logout" method="post" as="button" class="action-btn-danger">
                Log Out
            </Link>
        </template>
        <slot />
    </ProductLayout>
</template>
