<script setup>
import { computed } from 'vue';

const props = defineProps({
    label: {
        type: String,
        required: true,
    },
    variant: {
        type: String,
        default: 'default',
    },
});

const badgeClass = computed(() => {
    const variants = {
        default: 'bg-slate-100 text-slate-700',
        admin: 'bg-slate-900 text-white',
        teacher: 'bg-emerald-100 text-emerald-700',
        user: 'bg-blue-100 text-blue-700',
        locked: 'bg-amber-100 text-amber-700',
        danger: 'bg-red-100 text-red-700',
        success: 'bg-emerald-100 text-emerald-700',
    };

    return variants[props.variant] ?? variants.default;
});
</script>

<template>
    <span :class="badgeClass" class="inline-flex items-center rounded-full px-2.5 py-1 text-[10px] font-black uppercase tracking-[0.18em]">
        {{ label }}
    </span>
</template>
