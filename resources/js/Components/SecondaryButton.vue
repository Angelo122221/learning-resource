<script setup>
defineProps({
    type: {
        type: String,
        default: 'button',
    },
});
</script>

<template>
    <button
        :type="type"
        class="action-btn-secondary"
    >
        <slot />
    </button>
</template>
