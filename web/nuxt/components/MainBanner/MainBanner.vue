<script setup lang="ts">
const props = defineProps({
    image: {
        default: '',
        required: true
    },
    imageMobile: {
        default: ''
    }
});
</script>

<template>
    <div class="main-banner">
        <picture>
            <source v-if="imageMobile" media="(max-width: 768px)" :srcset="imageMobile" />
            <img :src="image" alt="banner" />
        </picture>
    </div>
</template>

<style lang="scss">
@import './main-banner.scss';
</style>
