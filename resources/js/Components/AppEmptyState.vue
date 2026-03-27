<script setup>
defineProps({
    title: {
        type: String,
        required: true,
    },
    message: {
        type: String,
        default: '',
    },
});
</script>

<template>
    <div class="rounded-[1.5rem] border-2 border-dashed border-slate-200 bg-slate-50 px-6 py-10 text-center">
        <p class="text-sm font-black uppercase tracking-[0.18em] text-slate-500">{{ title }}</p>
        <p v-if="message" class="mx-auto mt-3 max-w-2xl text-sm font-medium text-slate-500">{{ message }}</p>
        <div v-if="$slots.action" class="mt-5">
            <slot name="action" />
        </div>
    </div>
</template>
