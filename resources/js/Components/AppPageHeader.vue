<script setup>
defineProps({
    badge: {
        type: String,
        default: '',
    },
    title: {
        type: String,
        required: true,
    },
    accent: {
        type: String,
        default: '',
    },
    subtitle: {
        type: String,
        default: '',
    },
});
</script>

<template>
    <header class="mb-8 border-b-4 border-slate-200 pb-6">
        <div class="flex flex-col gap-5 lg:flex-row lg:items-end lg:justify-between">
            <div class="flex items-start gap-4 md:gap-5">
                <div v-if="badge" class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-blue-600 text-2xl font-black text-white shadow-lg shadow-blue-200">
                    {{ badge }}
                </div>

                <div class="min-w-0">
                    <h1 class="hero-title">
                        {{ title }}
                        <span v-if="accent" class="hero-accent"> {{ accent }}</span>
                    </h1>
                    <p v-if="subtitle" class="eyebrow mt-2">
                        {{ subtitle }}
                    </p>
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-3">
                <slot name="stats" />
                <slot name="actions" />
            </div>
        </div>
    </header>
</template>
