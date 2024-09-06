<template>
    <NuxtLink :to="props.link" class="article-link">
        <slot></slot>
        <span class="article-link__image section-mobile">
            <img :src="imageSrc" :alt="title" />
        </span>

        <span class="article-link__text d-block uppercase mt-12 spacing fw-medium">
            {{ title }}
        </span>
    </NuxtLink>
</template>

<script setup>
const props = defineProps({
    title: {
        type: String,
        default: ''
    },
    imageSrc: {
        type: String,
        default: ''
    },
    link: {
        type: String,
        default: '#'
    }
});
</script>

<style lang="scss">
@import './article-link.scss';
</style>
