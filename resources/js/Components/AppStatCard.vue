<script setup>
import { computed } from 'vue';

const props = defineProps({
    label: {
        type: String,
        required: true,
    },
    value: {
        type: [String, Number],
        required: true,
    },
    tone: {
        type: String,
        default: 'blue',
    },
});

const toneClass = computed(() => {
    const tones = {
        blue: 'text-blue-600',
        emerald: 'text-emerald-600',
        amber: 'text-amber-500',
        slate: 'text-slate-900',
        red: 'text-red-600',
    };

    return tones[props.tone] ?? tones.blue;
});
</script>

<template>
    <article class="panel px-4 py-4">
        <p class="text-[11px] font-black uppercase tracking-widest text-slate-400">{{ label }}</p>
        <p :class="toneClass" class="mt-2 text-3xl font-black">{{ value }}</p>
    </article>
</template>
