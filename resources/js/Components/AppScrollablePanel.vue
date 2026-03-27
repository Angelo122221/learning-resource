<script setup>
defineProps({
    heightClass: {
        type: String,
        default: 'max-h-[72vh]',
    },
    padded: {
        type: Boolean,
        default: true,
    },
});
</script>

<template>
    <div class="panel" :class="padded ? 'p-5 md:p-6' : ''">
        <div class="custom-scrollbar overflow-y-auto pr-2" :class="heightClass">
            <slot />
        </div>
    </div>
</template>
