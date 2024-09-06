<script setup lang="ts">
const props = defineProps({
    name: {
        default: '',
        required: true
    },
    image: {
        default: '',
        required: true
    },
    link: {
        default: '',
        required: true
    }
});
</script>

<template>
    <a :href="link" class="main-link">
        <img :src="image" :alt="name" />
        <span class="main-link__name text-center text-white uppercase spacing fw-medium">
            {{ name }}
        </span>
    </a>
</template>

<style lang="scss">
@import './main-link.scss';
</style>
