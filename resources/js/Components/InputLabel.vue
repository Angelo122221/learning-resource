<script setup>
defineProps({
    value: {
        type: String,
    },
});
</script>

<template>
    <label class="field-label block">
        <span v-if="value">{{ value }}</span>
        <span v-else><slot /></span>
    </label>
</template>
