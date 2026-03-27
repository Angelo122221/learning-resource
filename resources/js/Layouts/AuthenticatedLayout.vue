<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import UserLayout from '@/Layouts/UserLayout.vue';
import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';

const page = usePage();
const isAdmin = computed(() => Boolean(page.props.auth?.user?.is_admin));
</script>

<template>
    <component :is="isAdmin ? AdminLayout : UserLayout">
        <slot />
    </component>
</template>
