<script setup>
defineProps({
    message: {
        type: String,
    },
});
</script>

<template>
    <div v-show="message">
        <p class="mt-2 text-sm font-semibold text-red-600">
            {{ message }}
        </p>
    </div>
</template>
