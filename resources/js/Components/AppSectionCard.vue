<script setup>
defineProps({
    title: {
        type: String,
        required: true,
    },
    subtitle: {
        type: String,
        default: '',
    },
    padded: {
        type: Boolean,
        default: true,
    },
});
</script>

<template>
    <section class="panel" :class="padded ? 'p-5 md:p-6' : ''">
        <div class="mb-4 flex flex-col gap-2 border-b-2 border-slate-100 pb-4">
            <h2 class="text-xs font-black uppercase tracking-[0.24em] text-slate-400">{{ title }}</h2>
            <p v-if="subtitle" class="text-sm font-medium text-slate-500">{{ subtitle }}</p>
        </div>
        <slot />
    </section>
</template>
