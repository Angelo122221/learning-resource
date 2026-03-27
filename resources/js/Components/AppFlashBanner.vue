<script setup>
import { computed } from 'vue';

const props = defineProps({
    tone: {
        type: String,
        default: 'success',
    },
    message: {
        type: String,
        default: '',
    },
});

const toneClass = computed(() => {
    const tones = {
        success: 'border-emerald-200 bg-emerald-50 text-emerald-900',
        error: 'border-red-200 bg-red-50 text-red-900',
        info: 'border-blue-200 bg-blue-50 text-blue-900',
    };

    return tones[props.tone] ?? tones.info;
});
</script>

<template>
    <div v-if="message" :class="toneClass" class="mb-6 rounded-2xl border-2 px-4 py-3 text-sm font-semibold shadow-sm">
        {{ message }}
    </div>
</template>
