<script setup lang="ts">
import { ref, onErrorCaptured } from 'vue';

const error = ref();
const slots = useSlots();

const emit = defineEmits({
    error(_error) {
        return true;
    }
});

onErrorCaptured((err) => {
    emit('error', err);
    error.value = err;
    console.error(err);
    return false;
});

const render = () => (error.value ? slots.error?.({ error }) : slots.default?.());
</script>

<template>
    <render />
</template>
